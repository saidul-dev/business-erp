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
| `reason` | nullable — only populated on `adjustment_in`/`adjustment_out` rows, one of `App\Models\StockMovement::REASONS` (Stock Adjustment screen) |
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

Flow: pick a Site → two tables — **Products** (simple products) and
**Product Variants** (one row per variant of a `has_variants` product,
labelled "Product name — Variant label", e.g. "Premium Polo T-Shirt —
Black / M") — list everything that doesn't yet have **any** `stock_movements`
row at that site → enter quantity (+ unit cost, and batch/expiry/serial when
the *product* tracks them — those flags live on Product, not per-variant) →
one submit creates a `stock_movements` row per product/variant with a qty > 0.
Variant rows are stored with both `product_id` (the parent) and
`product_variant_id` set; simple-product rows leave `product_variant_id` null.

Guardrails:
- A product or variant drops off its list the moment it has *any* movement
  at that site — not just `initial_stock`. Once real transactions start
  (e.g. a Purchase), backdating an opening balance on top would double-count
  against them. Corrections go through a Stock Adjustment instead (see below).
- A `has_variants` product disappears from the Variants table entirely once
  every one of its variants has been seeded; if only some variants are
  seeded, the remaining ones still show under that product.
- Rows with an empty/zero quantity are skipped (lets you leave items for
  later without erroring the whole batch).

**Not yet covered:** literal CSV/file upload (current "bulk" entry is a
multi-row web form, not a file import).

## Stock Report (built)

Route: `admin/stock/report` (`stock.report`), gated behind `inventory.view`,
controller `App\Http\Controllers\Admin\StockReportController`.

Pick a Site → paginated table of every active, non-variant product with its
current balance at that site, computed live as
`SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END)` grouped by
`product_id` — never a stored counter. Status badge compares the balance
against `Product.reorder_level` (Out of Stock / Low Stock / In Stock).

Same `has_variants` exclusion as Initial Stock, for the same reason (no
per-variant ledger view yet).

## Stock Adjustment (built)

Route: `admin/stock/adjustment` (`stock.adjustment.index` / `stock.adjustment.store`),
gated behind the `inventory.edit` permission, controller
`App\Http\Controllers\Admin\StockAdjustmentController`.

Single-item correction, immediate effect (no approval step). Flow: pick a
Site → search a product or variant (any active one, not filtered by
movement history like Initial Stock) → its current balance at that site is
computed live and shown → choose Addition or Deduction, enter a quantity,
pick a fixed `reason` (`StockMovement::REASONS`) + optional note, optional
unit cost and batch/expiry/serial (only shown when the *product* tracks
them) → submit posts one `adjustment_in`/`adjustment_out` row.

Guardrail: a Deduction can't exceed the item's current balance at that site
(blocked with a validation error) — the ledger never goes negative.

Damage/expiry write-offs are **not** an Adjustment reason — they use the
separate `damage_expiry` type (see `inventory-movement-types.md`) once that
screen exists, so loss/wastage stays reportable on its own.
