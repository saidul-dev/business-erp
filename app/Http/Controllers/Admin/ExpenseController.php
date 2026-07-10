<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Expense;
use App\Models\LedgerAccount;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Day-to-day operating expenses (rent, utilities, salary, ...) booked
 * directly against a category account — unlike Purchase, there's no
 * Supplier/PO involved. Dr [category] / Cr [cash_bank account] via
 * LedgerService::post(). See docs/accounting-foundation.md.
 */
class ExpenseController extends Controller implements HasMiddleware
{
    /**
     * income_expense-group accounts that only ever get posted
     * automatically (by Purchase/Sale) — never offered as a manual
     * Expense category, even though they share the same group/nature as
     * the categories below.
     */
    protected const EXCLUDED_CATEGORY_CODES = ['purchase_expense', 'cost_of_goods_sold', 'discount_allowed'];

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

        $expenses = Expense::with(['category', 'paidFrom'])
            ->when($categoryId, fn ($q) => $q->where('category_account_id', $categoryId))
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.expenses.index', [
            'expenses' => $expenses,
            'categories' => $this->categoryOptions(),
            'categoryId' => $categoryId,
        ]);
    }

    public function create()
    {
        return view('admin.expenses.create', [
            'categories' => $this->categoryOptions(),
            'accounts' => LedgerAccount::where('group', 'cash_bank')->where('status', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_account_id' => ['required', 'integer', 'exists:ledger_accounts,id'],
            'paid_from_account_id' => ['required', 'integer', 'different:category_account_id', 'exists:ledger_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = LedgerAccount::where('group', 'income_expense')->findOrFail($validated['category_account_id']);
        $paidFrom = LedgerAccount::where('group', 'cash_bank')->findOrFail($validated['paid_from_account_id']);

        $expense = DB::transaction(function () use ($validated, $category, $paidFrom) {
            $expense = Expense::create([
                'expense_no' => 'PENDING',
                'category_account_id' => $category->id,
                'paid_from_account_id' => $paidFrom->id,
                'amount' => $validated['amount'],
                'expense_date' => $validated['expense_date'],
                'reference_no' => $validated['reference_no'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_by' => Auth::id(),
            ]);
            $expense->update(['expense_no' => 'EXP-'.str_pad($expense->id, 6, '0', STR_PAD_LEFT)]);

            LedgerService::post([
                'type' => 'expense',
                'date' => $validated['expense_date'],
                'narration' => "{$category->name} ({$expense->expense_no})",
                'reference' => $expense,
                'created_by' => Auth::id(),
                'lines' => [
                    ['account' => $category, 'debit' => $validated['amount']],
                    ['account' => $paidFrom, 'credit' => $validated['amount']],
                ],
            ]);

            return $expense;
        });

        return redirect()->route('expenses.show', $expense)->with('success', "Expense {$expense->expense_no} recorded.");
    }

    public function show(Expense $expense)
    {
        $expense->load(['category', 'paidFrom', 'creator', 'ledgerTransaction.lines.account']);

        return view('admin.expenses.show', compact('expense'));
    }

    public function print(Expense $expense)
    {
        $expense->load(['category', 'paidFrom', 'creator']);
        $company = CompanySetting::current();

        return view('admin.expenses.print', compact('expense', 'company'));
    }

    protected function categoryOptions(): Collection
    {
        return LedgerAccount::where('group', 'income_expense')
            ->where('nature', 'debit')
            ->where('status', true)
            ->whereNotIn('code', self::EXCLUDED_CATEGORY_CODES)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }
}
