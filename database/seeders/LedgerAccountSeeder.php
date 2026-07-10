<?php

namespace Database\Seeders;

use App\Models\LedgerAccount;
use Illuminate\Database\Seeder;

class LedgerAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            ['code' => 'cash_in_hand', 'name' => 'Cash in Hand', 'group' => 'cash_bank', 'nature' => 'debit'],
            ['code' => 'accounts_receivable', 'name' => 'Accounts Receivable', 'group' => 'party', 'nature' => 'debit'],
            ['code' => 'accounts_payable', 'name' => 'Accounts Payable', 'group' => 'party', 'nature' => 'credit'],
            ['code' => 'inventory', 'name' => 'Inventory', 'group' => 'inventory', 'nature' => 'debit'],
            ['code' => 'purchase_expense', 'name' => 'Purchase Expense', 'group' => 'income_expense', 'nature' => 'debit'],
            ['code' => 'sales_revenue', 'name' => 'Sales Revenue', 'group' => 'income_expense', 'nature' => 'credit'],
            ['code' => 'cost_of_goods_sold', 'name' => 'Cost of Goods Sold', 'group' => 'income_expense', 'nature' => 'debit'],
            ['code' => 'discount_allowed', 'name' => 'Discount Allowed', 'group' => 'income_expense', 'nature' => 'debit'],
            ['code' => 'opening_balance_equity', 'name' => 'Opening Balance Equity', 'group' => 'equity_adjustment', 'nature' => 'credit'],
        ];

        foreach ($accounts as $account) {
            LedgerAccount::firstOrCreate(
                ['code' => $account['code']],
                $account + ['is_system' => true]
            );
        }
    }
}
