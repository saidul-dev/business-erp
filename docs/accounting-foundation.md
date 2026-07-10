# Accounting Foundation

Design notes for the ledger that Cash & Bank, Party (Customer/Supplier), Purchase,
Sales, Expense/Income, and Equity/Opening-Balance will all post into. This is the
same "ledger is the single source of truth, balances are never a stored counter"
rule already proven out by [stock-movements.md](stock-movements.md) for
`stock_movements` — the accounting ledger below is that same pattern applied to
money instead of quantity, built as a **real (lightweight) double-entry ledger from
day one** so Phase 2 (Trial Balance, P&L, Balance Sheet, VAT, Payroll...) is purely
additive — new report screens reading existing rows, not a schema migration.

## Functional Account Groups

Every `ledger_accounts` row belongs to exactly one group:

| Group | Purpose | Examples |
|---|---|---|
| `cash_bank` | Company's cash and bank balances | Cash in Hand, Petty Cash, [Bank Name] - [A/C No.] |
| `party` | The two control accounts that every Party ledger line posts against | Accounts Receivable, Accounts Payable |
| `inventory` | Stock value and cost of goods | Inventory, Cost of Goods Sold |
| `income_expense` | Day-to-day business income and expense (one combined group, per the original spec) | Sales Revenue, Purchase Expense, Salary, Rent, Electricity, Other Income, Commission |
| `equity_adjustment` | Company opening/capital and manual corrections | Capital, Opening Balance Equity, Journal Adjustment |

(`LedgerAccount::GROUPS` in code.)

Individual **Customers/Suppliers are not rows in `ledger_accounts`** — a Party
already has its own identity in the `parties` table. Every ledger line concerning
a party points at one of the two `party`-group control accounts (Accounts
Receivable/Payable) **and** stamps `party_id`, so a party's statement is just
`ledger_transaction_lines` filtered by `party_id` — no duplicate bookkeeping
between the `parties` table and the ledger.

## Table: `ledger_accounts` (Chart of Accounts — lite)

| Column | Notes |
|---|---|
| `name` | e.g. "Cash in Hand", "City Bank – CA 1234", "Inventory", "Purchase Expense" |
| `code` | nullable, unique — free-form on user-added Bank accounts, but also the **stable lookup key** for the 6 system accounts (`accounts_payable`, `accounts_receivable`, ...). `LedgerService::post()` resolves a `'account' => 'accounts_payable'` line by this column, never by `name`. |
| `group` | one of the 5 groups above |
| `nature` | `debit`/`credit` — the account's normal balance side (Asset/Expense accounts are debit-normal, Liability/Equity/Income are credit-normal). Lets one generic query compute a correctly-signed balance for *any* account regardless of type |
| `is_system` | bool — protects the auto-seeded control accounts (Accounts Payable, Accounts Receivable, Inventory, Opening Balance Equity, Purchase Expense, Cash in Hand) from deletion. Renaming is still allowed. |
| `site_id` | nullable — **only meaningful for the `cash_bank` group**; every other group is forced back to `null` in `LedgerAccount::booted()` regardless of what's passed in |
| `status` | active/inactive, same toggle pattern as Site/Category/Brand |

Phase 1 does **not** ship a generic "add any account" CRUD screen — only a
seeder for the fixed system accounts (`LedgerAccountSeeder`) plus a small Bank
Accounts screen, scoped to the `cash_bank` group only, under **Accounts → Bank
Accounts** in the sidebar. Full free-form Chart of Accounts management is Phase 2.

## Table: `ledger_transactions` (voucher header)

One row per business event (mirrors `stock_transfers` being the header for
`stock_transfer_items`).

| Column | Notes |
|---|---|
| `voucher_no` | human-readable, auto-numbered |
| `date` | |
| `type` | `purchase` / `sale` / `payment_out` / `payment_in` / `expense` / `income` / `opening_balance` / `journal` — same fixed-const-array pattern as `StockMovement::TYPES` |
| `reference_type` / `reference_id` | nullable morph back to the source document (Purchase, Payment, Party for an opening balance, etc.) — identical convention to `stock_movements.reference_type/id` |
| `narration` | free text |
| `site_id` | **nullable**, unlike `stock_movements.site_id` — Purchase/Sale/Payment/Expense happen at a specific Site and should set it, but company-wide entries (a Party's opening balance, a manual Journal correction) have no single Site to attach to |
| `created_by` | nullable — system-generated postings (the opening-balance backfill command) have no authenticated user |

## Table: `ledger_transaction_lines` (the ledger itself)

| Column | Notes |
|---|---|
| `ledger_transaction_id` | FK to the header |
| `ledger_account_id` | FK to `ledger_accounts` — always set, including on party lines (points at Accounts Receivable/Payable) |
| `party_id` | nullable — set only when `ledger_account_id` is a `party`-group account |
| `debit` / `credit` | decimal(14,2) — exactly one of the two is non-zero per line |

**Balances are always derived, never a stored counter** — same rule as
`stock_movements`. `LedgerAccount::balance()` and `Party::lines()` implement
exactly this:

- **Account balance** = `SUM(debit) − SUM(credit)` for that `ledger_account_id`, sign
  read according to its `nature`.
- **Party due/advance** = the same query filtered to `ledger_account_id = Accounts
  Receivable/Payable AND party_id = X` — see `PartyController::ledger()`, which
  reports Receivable and Payable as two separate numbers since a
  Customer+Supplier party can carry both at once.
- **Cash/Bank balance** = the same generic account-balance query, just pointed at
  that specific `cash_bank` account.

A `ledger_transaction`'s lines must always satisfy `SUM(debit) = SUM(credit)` —
enforced in the posting service, not left to each caller.

## Central posting: `LedgerService::post()`

The **only** place allowed to write `ledger_transactions`/`ledger_transaction_lines`.
Every module hands it a small set of lines and gets a transaction back; no
controller ever touches a balance directly.

```
LedgerService::post([
    'type' => 'purchase',
    'site_id' => ...,
    'reference' => $purchase,
    'lines' => [
        ['account' => 'inventory', 'debit' => 1000],
        ['account' => 'accounts_payable', 'credit' => 1000, 'party_id' => $supplier->id],
    ],
]);
```

Wrapped in a DB transaction; rejects anything where debit ≠ credit. This is the
literal implementation of the "Single Source of Truth / No Manual Balance" rules —
Purchase, Sales, Payment, Expense controllers only ever *call* this, they never
increment a `balance` column themselves.

## Decisions

Resolved (previously "Open questions" — implemented as follows):

- **`site_id` on `ledger_accounts`**: optional even within `cash_bank` —
  admin's choice per account (blank = shared, e.g. the main bank account;
  set = branch-specific cash drawer). Every other group is force-nulled
  regardless of input (`LedgerAccount::booted()`). `ledger_transactions.site_id`
  is nullable for the same reason (company-wide entries like an opening
  balance have no Site).
- **Voucher numbering**: one global sequence per `type`, e.g. `PUR-000001`,
  `OB-000001` (`LedgerTransaction::PREFIXES`, `LedgerService::nextVoucherNo()`).
  Simple `count()+1`, not a locked sequence table — fine for this app's
  low-concurrency single-company usage; revisit only if that changes.
- **Cash-paid Purchase**: always posts through Accounts Payable, settled by a
  separate linked `payment_out` transaction — never skipped. Not yet relevant
  (Purchase isn't built), but `LedgerService::post()` already supports calling
  it twice with the same `reference`.

## Built (this pass)

- `ledger_accounts`, `ledger_transactions`, `ledger_transaction_lines`
  migrations + `LedgerAccount`/`LedgerTransaction`/`LedgerTransactionLine`
  models.
- `LedgerAccountSeeder` — the 6 system accounts (`cash_in_hand`,
  `accounts_receivable`, `accounts_payable`, `inventory`, `purchase_expense`,
  `opening_balance_equity`), called from `DatabaseSeeder`.
- `App\Services\LedgerService::post()` — the single posting entry point.
  Validates `SUM(debit) = SUM(credit)`, wraps in a DB transaction.
- **Bank Accounts** screen (`admin/bank-accounts`, `accounts.*` permissions —
  already existed, unused until now) — CRUD scoped to the `cash_bank` group
  only, under **Accounts → Bank Accounts** in the sidebar. Shows each
  account's live derived balance. System accounts can't be deleted; accounts
  with existing ledger lines can't be deleted either.
- **Party opening balance → ledger**: `Party::booted()` posts one
  `opening_balance` transaction the moment a Party is created with
  `opening_balance > 0` (Customer-only or dual-role → Accounts Receivable;
  Supplier-only → Accounts Payable, per `Party::postOpeningBalanceToLedger()`).
  `php artisan app:backfill-party-opening-balances` covers parties created
  before this existed. **Known gap**: editing `opening_balance` after
  creation does *not* adjust the ledger — that needs a reversing-entry
  mechanism, deferred until it's actually needed.
- **Party Ledger** page (`admin/parties/{party}/ledger`, linked from a new
  row-action icon on the Parties list) — read-only statement: Receivable and
  Payable shown as two separate derived numbers (a dual-role party can carry
  both), plus the full transaction history.
- `resources/lang/bn.json` — translations for all of the above.

## What's still needed before Purchase specifically

1. ~~`ledger_accounts` + seeder + Bank Accounts screen~~ — done.
2. ~~`ledger_transactions` + `ledger_transaction_lines` migrations~~ — done.
3. ~~`LedgerService::post()`~~ — done.
4. ~~Party ledger/statement page~~ — done.
5. **Purchase itself** — posts a `stock_movements` row (existing system,
   unchanged) **and** a `ledger_transactions` row (Dr Inventory, Cr Accounts
   Payable — or Cr Cash/Bank directly if paid immediately) via
   `LedgerService::post()`. Not started.

Not required before Purchase: a full Cash/Bank *register* (drill-down
transaction list per account, like the Party Ledger page has), Expense
module, generic Chart-of-Accounts screen, any Phase 2 report.

## Phase 1 scope (unchanged)

| Built now | Deferred to Phase 2+ |
|---|---|
| `ledger_accounts` (seeded system accounts + Bank Accounts CRUD) | Full free-form Chart of Accounts screen |
| `ledger_transactions` + `ledger_transaction_lines` + `LedgerService::post()` | Manual Journal Voucher entry screen |
| Party ledger/statement (due/advance + transaction history) | Trial Balance, Profit & Loss, Balance Sheet |
| Party opening balance → one `opening_balance` ledger transaction | Cash/Bank per-account register (drill-down list) |
| Purchase, Sales, Payment, Collection, Expense posting through `LedgerService` | VAT & Tax, Payroll, Fixed Asset, Loan Management |

Every Phase 2 report reads existing `ledger_transaction_lines` rows — no schema
change, just new aggregation queries and screens.
