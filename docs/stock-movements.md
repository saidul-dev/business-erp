# Stock Movements

Implementation notes for the `stock_movements` ledger — the single source of
truth for product stock, per Site. See [inventory-movement-types.md](inventory-movement-types.md)
for the full list of planned IN/OUT reasons this feeds into.

## Table: `stock_movements`

One row per movement. Current stock for a product at a site is always
`SUM(quantity) WHERE direction = 'in'` minus the same for `'out'` — never a
mutable counter column, so the full history stays auditable.

| Column | Notes |
|---|---|
| `product_id` | required |
| `product_variant_id` | nullable — only for variable products (not yet wired into Initial Stock, see below) |
| `site_id` | required — every movement is site-scoped |
| `type` | string, one of `App\Models\StockMovement::TYPES` keys |
| `direction` | `in`/`out` — **always derived from `type`** in `StockMovement::booted()`, never set directly, so a row can't disagree with its own type |
| `quantity` | decimal(14,4), always stored positive |
| `unit_cost` | decimal(14,4), nullable |
| `batch_no`, `expiry_date`, `serial_no` | nullable — only populated when the product's `track_batch`/`track_expiry`/`track_serial` flags are on |
| `reference_type` / `reference_id` | nullable morph — will link back to the source document (Purchase, Sale, Transfer, Adjustment) once those modules exist |
| `moved_at` | date the movement is effective as-of (can differ from `created_at`) |
| `created_by` | user who recorded it |

`App\Models\StockMovement::TYPES` is the fixed type → direction map (mirrors
the `Site::TYPES` const-array pattern already used in this codebase — not a
DB-backed lookup table, since the list isn't user-editable).

## Initial Stock feature (built)

Route: `admin/stock/initial-stock` (`stock.initial.index` / `stock.initial.store`),
gated behind the `inventory.create` permission, controller
`App\Http\Controllers\Admin\InitialStockController`.

Flow: pick a Site → a table lists every active, non-variant product that
doesn't already have an `initial_stock` movement at that site → enter
quantity (+ unit cost, and batch/expiry/serial when the product tracks them)
for as many rows as needed → one submit creates a `stock_movements` row per
product with a qty > 0.

Guardrails:
- A product/site pair that already has an `initial_stock` row is excluded
  from the list and silently skipped if resubmitted — opening stock is never
  overwritten. Corrections go through an Adjustment movement instead once
  that screen exists.
- Rows with an empty/zero quantity are skipped (lets you leave products for
  later without erroring the whole batch).

**Not yet covered:** variable (`has_variants`) products — they need a
per-variant version of this screen, and literal CSV/file upload (current
"bulk" entry is a multi-row web form, not a file import).
