<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Validates and runs the read-only SQL the AI Terminal's "run_sql" tool
 * generates. Only whitelisted business tables are reachable, only a single
 * SELECT statement is allowed, and any query touching a site-scoped table
 * (directly or one FK hop away, e.g. attendance_logs -> employees.site_id)
 * must filter by site_id whenever the admin has a site selected — this is
 * a best-effort guard on top of the table whitelist, not a full SQL parser.
 */
class AiSqlGuard
{
    protected const ALLOWED_TABLES = [
        'sales', 'sale_items', 'sale_returns', 'sale_return_items',
        'sale_deliveries', 'sale_delivery_items', 'sale_quotations', 'sale_quotation_items',
        'purchases', 'purchase_items', 'purchase_returns', 'purchase_return_items',
        'purchase_receipts', 'purchase_receipt_items', 'purchase_requisitions', 'purchase_requisition_items',
        'products', 'product_variants', 'categories', 'brands', 'units', 'attributes', 'attribute_values',
        'stock_movements', 'stock_transfers', 'stock_transfer_items',
        'parties', 'payments', 'collections',
        'expenses', 'incomes', 'fund_transfers', 'capital_transactions',
        'ledger_accounts', 'ledger_transactions', 'ledger_transaction_lines',
        'sites',
        'employees', 'departments', 'designations', 'attendance_logs',
        'leave_types', 'leave_balances', 'leave_requests',
        'salary_structures', 'payroll_runs', 'payroll_run_items',
        'delivery_zones', 'courier_consignments',
        'projects', 'milestones', 'tasks', 'task_comments', 'task_time_logs',
    ];

    protected const FORBIDDEN_KEYWORDS = [
        'insert', 'update', 'delete', 'drop', 'alter', 'truncate', 'grant', 'revoke',
        'create', 'replace', 'merge', 'call', 'exec', 'execute',
        'into outfile', 'into dumpfile', 'load_file',
        'information_schema', 'performance_schema', 'sleep(', 'benchmark(',
    ];

    public function schemaDescription(): string
    {
        $lines = [];

        foreach ($this->introspectSchema() as $table => $info) {
            $lines[] = "{$table}(".implode(', ', $info['columns']).')';

            foreach ($info['foreign_keys'] as $fk) {
                $lines[] = '  FK: '.implode(',', $fk['columns'])." -> {$fk['foreign_table']}";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @return array{row_count: int, rows: array<int, array<string, mixed>>}|array{error: string}
     */
    public function run(string $sql, ?int $siteId): array
    {
        $sql = rtrim(trim($sql), "; \t\n\r");

        if ($sql === '') {
            return ['error' => 'Empty query.'];
        }

        if (str_contains($sql, ';')) {
            return ['error' => 'Only a single SQL statement is allowed.'];
        }

        if (! preg_match('/^select\s/i', $sql)) {
            return ['error' => 'Only SELECT statements are allowed.'];
        }

        if (str_contains($sql, '--') || str_contains($sql, '/*')) {
            return ['error' => 'SQL comments are not allowed.'];
        }

        $lower = mb_strtolower($sql);

        foreach (self::FORBIDDEN_KEYWORDS as $keyword) {
            if (preg_match('/\b'.preg_quote($keyword, '/').'\b/i', $lower)) {
                return ['error' => "Query rejected: disallowed keyword \"{$keyword}\"."];
            }
        }

        $schema = $this->introspectSchema();
        $tables = $this->referencedTables($sql);

        if (empty($tables)) {
            return ['error' => 'Could not determine which tables this query uses.'];
        }

        $disallowed = array_diff($tables, array_keys($schema));

        if (! empty($disallowed)) {
            return ['error' => 'Query rejected: table(s) not allowed: '.implode(', ', $disallowed)];
        }

        $scopedTables = $this->siteScopedTables($schema);

        if ($siteId !== null && array_intersect($tables, $scopedTables) && ! str_contains($lower, 'site_id')) {
            return ['error' => 'Query rejected: must filter by site_id for the current site.'];
        }

        if (! preg_match('/\blimit\s+\d+/i', $sql)) {
            $sql .= ' LIMIT 100';
        }

        // MySQL optimizer hint, not a comment we forbid above — added after
        // validation so the AI can't smuggle its own comment/hint through it.
        $sql = preg_replace('/^select\s/i', 'SELECT /*+ MAX_EXECUTION_TIME(5000) */ ', $sql, 1);

        try {
            $rows = DB::select($sql);
        } catch (\Throwable $e) {
            Log::warning('AI terminal SQL fallback failed', ['sql' => $sql, 'message' => $e->getMessage()]);

            return ['error' => 'Query failed to execute.'];
        }

        $rows = array_map(fn ($row) => (array) $row, array_slice($rows, 0, 50));

        return ['row_count' => count($rows), 'rows' => $rows];
    }

    protected function referencedTables(string $sql): array
    {
        preg_match_all('/\b(?:from|join)\s+`?(\w+)`?/i', $sql, $matches);

        return array_values(array_unique(array_map('mb_strtolower', $matches[1] ?? [])));
    }

    /**
     * Tables with a site_id column, plus tables that reach one via a single
     * foreign-key hop (e.g. attendance_logs -> employees -> site_id).
     */
    protected function siteScopedTables(array $schema): array
    {
        $scoped = [];

        foreach ($schema as $table => $info) {
            if (in_array('site_id', $info['columns'], true)) {
                $scoped[$table] = true;
            }
        }

        $changed = true;

        while ($changed) {
            $changed = false;

            foreach ($schema as $table => $info) {
                if (isset($scoped[$table])) {
                    continue;
                }

                foreach ($info['foreign_keys'] as $fk) {
                    if (isset($scoped[$fk['foreign_table']])) {
                        $scoped[$table] = true;
                        $changed = true;
                        break;
                    }
                }
            }
        }

        return array_keys($scoped);
    }

    /**
     * @return array<string, array{columns: array<int, string>, foreign_keys: array<int, array{columns: array<int, string>, foreign_table: string}>}>
     */
    protected function introspectSchema(): array
    {
        return Cache::remember('ai_terminal_sql_schema', 3600, function () {
            $schema = [];

            foreach (self::ALLOWED_TABLES as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $foreignKeys = array_values(array_filter(array_map(fn ($fk) => [
                    'columns' => $fk['columns'],
                    'foreign_table' => $fk['foreign_table'],
                ], Schema::getForeignKeys($table)), fn ($fk) => in_array($fk['foreign_table'], self::ALLOWED_TABLES, true)));

                $schema[$table] = [
                    'columns' => array_map(fn ($col) => $col['name'], Schema::getColumns($table)),
                    'foreign_keys' => $foreignKeys,
                ];
            }

            return $schema;
        });
    }
}
