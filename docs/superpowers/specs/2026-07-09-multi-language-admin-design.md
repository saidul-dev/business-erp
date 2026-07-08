# Multi-language support for the admin panel

## Goal

Add English/Bangla language switching to the admin panel. Default locale is English; switching to Bangla translates all static UI text (navigation, buttons, headers, labels, flash messages) without touching data-driven content or Laravel's built-in validation messages.

## Scope

- **In scope:** the full admin panel — shared layout (`resources/views/layouts/app.blade.php`) and all 36 module views under `resources/views/admin/**` (dashboard, products, categories, brands, units, attributes, users, roles, sites, settings).
- **Out of scope:** the public storefront (`resources/views/website/**`), Laravel's built-in validation error strings (stay in English), and translation of dynamic/DB-driven content (product names, category names, any user-entered data).

## Architecture

### Locale resolution

- `config/app.php` gets a new `available_locales` entry: `['en' => 'English', 'bn' => 'বাংলা']`.
- New middleware `App\Http\Middleware\SetLocale`, mirroring the existing `SetCurrentSite` pattern (`app/Http/Middleware/SetCurrentSite.php`):
  - Reads `session('locale')`.
  - If it's a valid key in `config('app.available_locales')`, calls `App::setLocale($locale)`.
  - Otherwise leaves the app on `config('app.locale')` (`en`), no session write.
- Registered as a global `web`-group middleware (`$middleware->web(append: [...])` in `bootstrap/app.php`), so it applies to every request including guest/auth pages, consistent with how the rest of the app resolves cross-cutting state.

### Switching locale

- `POST /language/{locale}` → `App\Http\Controllers\LanguageController@switch`.
  - Validates `{locale}` against `config('app.available_locales')`; aborts 404 on anything else (no arbitrary session injection).
  - Stores `session(['locale' => $locale])`.
  - Redirects back (`redirect()->back()`).
  - Route sits outside `auth` middleware (locale is a UI preference, not an authenticated action) but is still whitelist-validated.
- Session-only persistence — no DB migration, no per-user column. Resets on cleared cookies/new browser, which is acceptable per user decision.

### Switcher UI

- Small globe-icon dropdown added to the topbar in `layouts/app.blade.php`, positioned to the left of the existing Site selector dropdown (same `x-data="{ open: false }"` Alpine pattern already used there and in the user menu).
- Lists "English" / "বাংলা" (from `config('app.available_locales')`), current one checked, each a tiny `POST` form to `/language/{locale}`.

## Translation strings

- Uses Laravel's **JSON translation files** (the mechanism Breeze's scaffolded auth views already use via `__('Password')`, `__('Email')`, etc.) rather than PHP array files with invented dot-notation keys.
- The visible English string *is* the translation key: `{{ __('Products') }}`.
- `resources/lang/bn.json` holds the Bangla value for every wrapped string, e.g. `"Products": "পণ্য"`.
- No `en.json` is needed: when no match is found for the active locale, `__()` returns the key itself, which is already correct English — so the default locale requires zero additional files and can never drift out of sync with the source text.
- Every hardcoded static string in the shared layout and the 36 admin views is wrapped in `__()`: nav section labels and links, dropdown items, topbar labels, page titles/headers, table column headers, buttons, form field labels/placeholders, empty-state text, confirm-dialog text, and flash/status messages (`session('success')` / `session('error')` banners already interpolate controller-set strings — those controller-side strings get wrapped in `__()` at the source too).
- Dynamic content (site names, user names, product/category/brand names, role names, any `{{ $model->attribute }}` output) is left as-is.
- Laravel's own validation messages (`required`, `email`, `unique`, etc.) are **not** translated — they continue to render in English via the framework's built-in defaults, since no `lang/en/validation.php` is published and none will be.

## Rollout plan (module-by-module)

1. **Infrastructure**: config entry, `SetLocale` middleware + registration, `LanguageController` + route, empty `resources/lang/bn.json` scaffold.
2. **Shared layout**: wrap + translate `layouts/app.blade.php` (sidebar nav, topbar, switcher itself, footer) and `layouts/guest.blade.php` if it carries static chrome. Add switcher UI here.
3. **Dashboard**: wrap + translate `resources/views/dashboard.blade.php` (and any dashboard-only partials).
4. **Inventory module group**: products, categories, brands, units, attributes (5 view sets).
5. **Administration module group**: users, roles, sites, settings (4 view sets).

Each step wraps the relevant view(s) in `__()` and appends the corresponding entries to `resources/lang/bn.json` in the same step, so the app is always in a working, reviewable state — never left with wrapped-but-untranslated strings.

## Testing

No automated test suite is being added per project convention (user writes/commits tests themselves). Manual verification per rollout step: load the affected page(s) in English (default), switch to Bangla via the new switcher, confirm the UI text renders in Bangla and dynamic data is unaffected, switch back to English, confirm the switcher persists across navigation within the session.
