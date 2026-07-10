<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Income;
use App\Models\LedgerAccount;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Miscellaneous income (interest, rent received, commission, ...) booked
 * directly against a category account — the symmetric counterpart to
 * ExpenseController. Dr [cash_bank account] / Cr [category] via
 * LedgerService::post(). See docs/accounting-foundation.md.
 */
class IncomeController extends Controller implements HasMiddleware
{
    /**
     * income_expense-group accounts that only ever get posted
     * automatically (by Sale) — never offered as a manual Income
     * category, even though it shares the same group/nature as the
     * categories below.
     */
    protected const EXCLUDED_CATEGORY_CODES = ['sales_revenue'];

    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounts.view', only: ['index', 'show', 'print']),
            new Middleware('permission:accounts.create', only: ['create', 'store']),
        ];
    }

    public function index(Request $request)
    {
        $categoryId = $request->filled('category_account_id') ? $request->integer('category_account_id') : null;

        $incomes = Income::with(['category', 'receivedInto'])
            ->when($categoryId, fn ($q) => $q->where('category_account_id', $categoryId))
            ->orderByDesc('income_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.incomes.index', [
            'incomes' => $incomes,
            'categories' => $this->categoryOptions(),
            'categoryId' => $categoryId,
        ]);
    }

    public function create()
    {
        return view('admin.incomes.create', [
            'categories' => $this->categoryOptions(),
            'accounts' => LedgerAccount::where('group', 'cash_bank')->where('status', true)->orderBy('name')->get(['id', 'name']),
            'monthTotal' => Income::whereMonth('income_date', now()->month)->whereYear('income_date', now()->year)->sum('amount'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_account_id' => ['required', 'integer', 'exists:ledger_accounts,id'],
            'received_into_account_id' => ['required', 'integer', 'different:category_account_id', 'exists:ledger_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'income_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = LedgerAccount::where('group', 'income_expense')->findOrFail($validated['category_account_id']);
        $receivedInto = LedgerAccount::where('group', 'cash_bank')->findOrFail($validated['received_into_account_id']);

        $income = DB::transaction(function () use ($validated, $category, $receivedInto) {
            $income = Income::create([
                'income_no' => 'PENDING',
                'category_account_id' => $category->id,
                'received_into_account_id' => $receivedInto->id,
                'amount' => $validated['amount'],
                'income_date' => $validated['income_date'],
                'reference_no' => $validated['reference_no'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_by' => Auth::id(),
            ]);
            $income->update(['income_no' => 'INC-'.str_pad($income->id, 6, '0', STR_PAD_LEFT)]);

            LedgerService::post([
                'type' => 'income',
                'date' => $validated['income_date'],
                'narration' => "{$category->name} ({$income->income_no})",
                'reference' => $income,
                'created_by' => Auth::id(),
                'lines' => [
                    ['account' => $receivedInto, 'debit' => $validated['amount']],
                    ['account' => $category, 'credit' => $validated['amount']],
                ],
            ]);

            return $income;
        });

        return redirect()->route('incomes.show', $income)->with('success', "Income {$income->income_no} recorded.");
    }

    public function show(Income $income)
    {
        $income->load(['category', 'receivedInto', 'creator', 'ledgerTransaction.lines.account']);

        return view('admin.incomes.show', compact('income'));
    }

    public function print(Income $income)
    {
        $income->load(['category', 'receivedInto', 'creator']);
        $company = CompanySetting::current();

        return view('admin.incomes.print', compact('income', 'company'));
    }

    protected function categoryOptions(): Collection
    {
        return LedgerAccount::where('group', 'income_expense')
            ->where('nature', 'credit')
            ->where('status', true)
            ->whereNotIn('code', self::EXCLUDED_CATEGORY_CODES)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }
}
