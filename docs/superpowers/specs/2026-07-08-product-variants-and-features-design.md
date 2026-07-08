# Product Variants & Industry-Standard Features — Design

**Date:** 2026-07-08
**Status:** Approved for planning
**Author:** Saidul Islam (with Claude)

## Problem

Products currently carry only flat attributes (name, SKU, barcode, category, brand,
multi-unit, pricing). Real-world inventory needs industry-standard product features:

- **Variants** — one product sold in several Color/Size combinations, each with its own
  price, SKU, and barcode.
- **Batch tracking** — goods handled in batches at purchase/production time.
- **Expiry date** — perishable goods; expiry belongs to a batch.
- **Serial / IMEI** — unit-level unique identifiers (phones, electronics).
- **Warranty / Guarantee** — a coverage duration attached to the product.

These features must be **opt-in per product**: on the create/edit form each appears as a
checkbox, **unchecked by default**. Enabling a checkbox reveals that feature's inputs.

## Scope Decision

Purchase, Production, and Stock tracking **do not exist yet** in this codebase (they are
marked as future work in existing migrations/comments). The actual per-batch / per-serial
data is captured *during purchase or production*, which is not being built now.

Therefore this spec delivers:

1. **Variants — fully built now.** Global attributes, per-product variant generation, and
   per-variant full price/SKU/barcode.
2. **Batch / Expiry / Serial — product-level toggle flags only.** The booleans are stored
   on the product so the intent is recorded; actual batch/serial data capture is deferred
   to when Purchase/Production is built.
3. **Warranty / Guarantee — duration fields** (checkbox + period + unit) stored on the
   product.

Out of scope (documented as follow-ups): Purchase/Production/Stock; adding variants to the
barcode-print page.

## Decisions (from brainstorming)

| Question | Decision |
|---|---|
| Overall scope | Variants full now; batch/expiry/serial as toggle flags; warranty/guarantee as durations |
| Variant attribute model | Global attributes + values (Shopify/WooCommerce style), not per-product free-form |
| Variant price model | Per-variant full selling price (base product price is a default/prefill) |
| Warranty/Guarantee | Checkbox + duration (number) + unit (days/months/years); null = disabled |
| Expiry vs Batch | Enabling Expiry auto-enables Batch (expiry lives on a batch) |
| Attributes admin permission | Reuse existing `inventory.*` permissions (no new permission group) |

## Data Model

### New tables

**`attributes`** — global, reusable (managed like Category/Brand)
- `id`
- `name` string, unique (e.g. "Color", "Size")
- `timestamps`

**`attribute_values`**
- `id`
- `attribute_id` FK → attributes, cascade on delete
- `value` string (e.g. "Red", "M")
- `sort_order` unsignedInteger default 0
- `timestamps`
- unique(`attribute_id`, `value`)

**`product_variants`**
- `id`
- `product_id` FK → products, cascade on delete
- `sku` string, unique
- `barcode` string, nullable, unique
- `selling_price` decimal(12,2) default 0
- `estimated_cost` decimal(12,2) nullable
- `status` boolean default true
- `timestamps`

**`product_variant_values`** — which attribute values compose a variant
- `id`
- `product_variant_id` FK → product_variants, cascade on delete
- `attribute_id` FK → attributes, restrict on delete
- `attribute_value_id` FK → attribute_values, restrict on delete
- unique(`product_variant_id`, `attribute_id`)

### New columns on `products` (additive migration — existing data safe)

- `has_variants` boolean, default false
- `track_batch` boolean, default false
- `track_expiry` boolean, default false  *(UI auto-enables track_batch when set)*
- `track_serial` boolean, default false  *(serial / IMEI)*
- `warranty_period` unsignedInteger, nullable
- `warranty_unit` string, nullable  *(days | months | years)*
- `guarantee_period` unsignedInteger, nullable
- `guarantee_unit` string, nullable

`null` warranty_period / guarantee_period means the feature is disabled — no extra boolean
column needed.

### Semantics

- **`has_variants = true`**: the sellable entities are the `product_variants`. The base
  product's `selling_price` acts only as a default used to prefill new variant rows.
- **`has_variants = false`**: the product itself is the sellable unit (unchanged behaviour).
- **Batch / Expiry / Serial flags**: recorded intent only. No batch/serial tables now; they
  will be added with Purchase/Production.

## Models

- `Attribute` — `hasMany(AttributeValue)`; `hasMany` variant-value links.
- `AttributeValue` — `belongsTo(Attribute)`.
- `ProductVariant` — `belongsTo(Product)`; `hasMany(ProductVariantValue)` (or
  `belongsToMany` attribute values through the pivot). Accessor for a human label
  ("Red / M").
- `Product` additions — `hasMany(ProductVariant $variants)`; casts for the new boolean
  columns; `isVariable()` helper; accessors for warranty/guarantee display labels.

## UI / Form Flow

All new controls live in the shared `resources/views/admin/products/_form.blade.php`,
driven by Alpine.js (already used in the form). New card: **"Product Type & Features"**,
all checkboxes default OFF.

Checkboxes:
- ☐ **This product has variants** (`has_variants`)
- ☐ **Track batches** (`track_batch`)
- ☐ **Track expiry date** (`track_expiry`) — checking it also checks/locks Track batches
- ☐ **Track serial / IMEI** (`track_serial`)
- ☐ **Has warranty** — reveals period + unit
- ☐ **Has guarantee** — reveals period + unit

Revealed sections (Alpine `x-show`):

- **Variants ON:**
  1. Attribute pickers — choose one or more global attributes (Color, Size) and, per
     attribute, which values apply.
  2. **"Generate Variants"** button — builds the cartesian product of chosen values into an
     editable table. Each row: computed label (read-only), `sku`, `barcode`, `selling_price`
     (prefilled from base price), `estimated_cost` (optional), active toggle.
  3. Regenerating merges: keep existing rows whose value-combo still applies, add new combos,
     drop removed ones (no silent loss of already-entered prices where the combo persists).
- **Warranty / Guarantee ON:** number input + unit `<select>` (days/months/years).
- **Batch / Expiry / Serial:** checkbox only, with a small helper note: *"Batch/serial
  details are captured during purchase or production (coming soon)."*

When `has_variants` is unchecked, the base Pricing card behaves as today. When checked, the
base selling price is labelled as the default/prefill for variants.

## Backend

### Routes (`routes/web.php`, inside the existing inventory master-data group)
```
Route::resource('attributes', AttributeController::class)->except('show');
```

### AttributeController (new — mirrors BrandController)
- `HasMiddleware` with the same `inventory.view/create/edit/delete` mapping.
- `index` / `create` / `store` / `edit` / `update` / `destroy`.
- Attribute **values** are managed inline on the attribute create/edit page: a repeatable
  list of value rows submitted as `values[]`, synced (create/update/delete) inside a DB
  transaction.
- `destroy` guards against deletion while any product variant references the attribute.

### ProductController changes
- `formOptions()` also returns `attributes` (with values eager-loaded) for the variant UI.
- `validated()` extends with:
  - the new boolean flags (`boolean` rules);
  - `warranty_period`/`guarantee_period` (`nullable|integer|min:0`) and their units
    (`nullable|in:days,months,years`), required-with their period;
  - nested `variants` array — each with `sku` (required, distinct, unique in
    `product_variants` ignoring the row's own id on update), `barcode`
    (nullable, unique similarly), `selling_price` (numeric ≥ 0), `estimated_cost`
    (nullable numeric ≥ 0), `status` (boolean), and `values` (attribute_id →
    attribute_value_id map).
  - Conditional: variant rules apply only when `has_variants` is true; at least one variant
    required in that case.
- `store` / `update` wrapped in a `DB::transaction`:
  1. save product (flags included);
  2. if `has_variants`, sync variants — create new, update existing by id, delete removed —
     and their `product_variant_values` rows; if not, delete any existing variants.
- `track_expiry` implies `track_batch` — enforced server-side too (not just UI).

### Validation notes
- Variant SKU/barcode uniqueness is enforced within `product_variants`. Base-product SKU
  stays unique within `products`. Cross-table global uniqueness (so a scan resolves to one
  entity) is a known refinement, deferred; noted here so it is a conscious choice.

## Testing

- **Migrations** run cleanly and are reversible (`down` drops new tables/columns).
- **Attribute CRUD**: create attribute with values; edit adds/removes values; delete blocked
  while referenced.
- **Product with variants**: create product with `has_variants`, two attributes; generated
  variants persist with per-variant price/SKU/barcode and correct attribute-value links.
- **Variant re-sync on update**: adding/removing an attribute value adds/removes the right
  variant rows and keeps untouched rows' data.
- **Flags**: batch/expiry/serial booleans and warranty/guarantee period+unit persist;
  enabling expiry forces batch on server-side; disabling warranty nulls the period.
- **Non-variant product**: unchanged behaviour; existing product tests still pass.

## Follow-ups (out of scope)

- Purchase / Production / Stock modules — where batch/expiry/serial data is actually
  captured.
- Variants on the barcode-print page and product index (variant count badge).
- Cross-table SKU/barcode uniqueness.
