# Product Barcode Print — Design

**Date:** 2026-07-08
**Status:** Approved

## Purpose

Let a user print a scannable barcode label for a single product, straight from the
Products list, in a form that works both on a thermal sticker-label printer and on
a normal office printer using A4 paper.

## Scope

- Single-product print only (no multi-select bulk printing — out of scope for this
  spec; can be added later as its own feature if needed).
- Label content: barcode, product name, selling price.
- Two print layouts, user-selectable at print time: sticker roll and A4 grid.

## Current state (relevant facts)

- `Product` ([app/Models/Product.php](../../../app/Models/Product.php)) already has a
  nullable, unique `barcode` string column
  ([migration](../../../database/migrations/2026_07_08_095300_create_products_table.php)),
  alongside a required unique `sku`. Today `barcode` is just free text the user
  types in on the product form — nothing renders it as an actual barcode.
- No barcode/QR/PDF library exists anywhere in the stack (checked `composer.json`
  and `package.json`). This is greenfield.
- No print-oriented view exists anywhere in `resources/views` — no invoice/receipt
  print precedent to follow. This will be the first.
- Product routes are a resource route without `show`
  ([routes/web.php](../../../routes/web.php)):
  `Route::resource('products', ProductController::class)->except('show')`, plus a
  custom `PATCH .../toggle-status`. `ProductController` already follows this
  "small extra method on the resource controller" pattern for `toggleStatus`.
- The products index table
  ([resources/views/admin/products/index.blade.php](../../../resources/views/admin/products/index.blade.php))
  has a row-actions cell (Edit + Delete icon buttons, each gated by
  `@can('inventory.edit')` / `@can('inventory.delete')`) — this is where the new
  "Print Barcode" icon goes.
- Ten demo products now exist in the seeded DB (`ProductSeeder`), each with a
  unique `sku` and `barcode`, ready to exercise this feature.

## Approach

**Client-side barcode rendering with the `jsbarcode` npm package, printed via the
browser's native print dialog.**

Rejected alternative: server-side PHP generation (`picqer/php-barcode-generator` or
similar), rendering `<img>` tags. Rejected because it adds a Composer dependency
and a GD/Imagick requirement, requires a server round-trip on every quantity/layout
change, and produces raster output — `jsbarcode` renders directly to SVG, so it
stays crisp at any print DPI and updates instantly in the browser as the user
changes quantity or layout, with zero extra dependencies beyond one npm package
already in a stack that uses Alpine.js for exactly this kind of client-side
interactivity.

Barcode symbology: **Code128** — encodes arbitrary alphanumeric text, so it works
whether `barcode` holds a real EAN/UPC value or is empty and we fall back to `sku`.

## Design

### 1. Entry point

Add a "Print Barcode" icon button to the row-actions cell in
`admin/products/index.blade.php`, next to Edit/Delete, following the same icon-link
markup pattern (`rounded-lg p-2 text-slate-400 hover:bg-brand-50 hover:text-brand-700`).
Gated by `@can('inventory.view')` since printing is a read-only action — any user
who can see the product list can print its label.

### 2. Route & controller

New route, alongside the existing `toggle-status` custom route:

```php
Route::get('products/{product}/barcode', [ProductController::class, 'barcodePrint'])
    ->name('products.barcode');
```

`ProductController::barcodePrint(Product $product)` returns a new standalone view
`admin.products.barcode-print`, passing the product. No new controller class —
consistent with how `toggleStatus` already lives on `ProductController`.

### 3. Barcode value resolution

```php
$value = $product->barcode ?: $product->sku;
```

Computed once server-side and passed into the view (as a data attribute for JS to
read), so the fallback logic lives in one place and isn't duplicated in JS.

### 4. Print preview page

New view: `resources/views/admin/products/barcode-print.blade.php`.

- **Not** wrapped in `x-app-layout` — this is a dedicated, print-focused page (no
  sidebar/nav chrome), opened by navigating from the product list. A "← Back to
  Products" link covers navigation back.
- **On-screen controls** (hidden in print via `.no-print` + `@media print { display:
  none }`):
  - Quantity input, number, default `1`, min `1`.
  - Layout toggle: two radio buttons / segmented control — "Sticker Roll" and
    "A4 Sheet". Default: Sticker Roll.
  - "Print" button → calls `window.print()`.
- **Label markup**: an Alpine.js component (`x-data`) holds `quantity` and
  `layout`. An `x-for`-equivalent loop (Alpine `<template x-for>`) renders one
  label `<div>` per unit of quantity. Each label div contains:
  - An `<svg>` element that JsBarcode renders into (via `JsBarcode(el, value, {format: "CODE128", ...})`), re-run whenever quantity changes (new elements need rendering) using `$nextTick`.
  - Product name (truncated with CSS `text-overflow` if it doesn't fit).
  - Price, formatted as `৳{{ number_format($product->selling_price, 2) }}`.
- **Container** gets a `data-layout` attribute (or a class) bound to the Alpine
  `layout` value, which the print CSS below keys off.

### 5. Print CSS — two layouts

```css
@media print {
  .no-print { display: none !important; }

  /* Sticker Roll: one label per physical page, sized to the label stock */
  .labels[data-layout="roll"] {
    display: block;
  }
  .labels[data-layout="roll"] .label {
    width: 50mm;
    height: 25mm;
    page-break-after: always;
  }
  @page roll {
    size: 50mm 25mm;
    margin: 0;
  }

  /* A4 Sheet: grid of labels flowing across a normal page */
  .labels[data-layout="a4"] {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 4mm;
  }
  .labels[data-layout="a4"] .label {
    width: 50mm;
    height: 25mm;
  }
  @page a4 {
    size: A4;
    margin: 10mm;
  }
}
```

(`@page` named-page binding via a `page` CSS property on the layout container
selects the right page box per layout; exact mechanism to be finalized during
implementation — the plan should verify cross-browser behavior, since named pages
are a Chromium-only feature. Fallback: if named pages prove unreliable, drive
`@page size` off a `<body>`-level class set by Alpine instead of per-layout named
pages.)

50mm × 25mm is the standard small retail barcode label size — a reasonable default
for the sticker roll layout. Three columns for A4 keeps labels legible on a normal
page; both numbers can be tuned during implementation if they don't look right in a
real print preview.

### 6. Dependencies

- Add `jsbarcode` to `package.json` (npm), imported in the barcode-print view (or
  bundled via the existing Vite entry, whichever keeps the main app bundle from
  growing for a page most users rarely visit — decide during implementation).

## Error handling / edge cases

- `barcode` and `sku` are both guaranteed non-empty in practice (`sku` is a
  required field), so the fallback always has a value — no "no barcode available"
  state needs designing.
- Very long product names: truncate visually (CSS), not in the data — the full
  name should still be in the DOM for accessibility/copy-paste.
- Quantity input: clamp to a sane minimum (1) client-side; no server-side quantity
  validation needed since quantity never reaches the server (it's a client-side
  render count).

## Out of scope (explicitly not building)

- Bulk/multi-product barcode printing.
- Saving/exporting the barcode as a standalone image file.
- Custom label sizes beyond the two built-in layouts.
- Server-side barcode image generation.
