# Restaurant ERP SaaS — Product Roadmap & Build Reference

**Purpose of this doc:** a working reference to build from, not a spec. No
per-feature design docs — pick an item, build it, commit, tick it off here.
Update the status marker when a feature ships; add a one-line note only if
something genuinely needs remembering (a decision, a deferred piece).

Status markers: `[ ]` not started · `[~]` partially there · `[x]` done.

---

## Review notes (gaps/risks in the master list, read this first)

1. **Multi-Tenant SaaS is the single biggest piece of work here and it's
   listed as one bullet.** Everything else in this list lives *inside* a
   tenant. It needs its own section, not a line under Authentication. See
   "Tenancy & Billing" below — this has to be built before or alongside
   Branch Management, not after MVP.
2. **No Subscription/Package/Billing section exists anywhere in the
   original list**, even though the whole business model depends on it
   ("1 month free, then must go premium"). Added as its own MVP section
   below — Plan, Subscription, trial countdown, branch-limit enforcement,
   upgrade/downgrade, payment collection.
3. **MVP (v1.0) is really a full v1.0 launch, not a minimal MVP** — POS +
   KDS + Recipe/BOM + Loyalty/Wallet/Membership + full accounting + 10
   report types, all in one phase. That's fine as the actual v1.0 target,
   but build order matters: get Tenancy → Branch → Menu → core POS (dine-in
   + takeaway, one payment method, print) → basic Inventory running
   end-to-end first, before breadth (loyalty, combo meals, happy-hour
   pricing, 10 report types).
4. **Payment Gateway is only listed under Future/API & Integrations**, but
   v1.5's "Online Payments" needs at least one gateway (SSLCommerz/bKash are
   the common Bangladesh choices) working by v1.5, not deferred to Future.
5. **"AI Features" and "Magical Features (USP)" heavily overlap** (AI
   Business Copilot / AI Restaurant Copilot, AI Inventory / AI Smart
   Inventory Planning, AI Kitchen / AI Kitchen Optimization, AI Pricing / AI
   Auto Promotions, AI Reviews / AI Complaint Analyzer). Treat "Magical
   Features" as the *marketing framing* of a subset of "AI Features," not
   separate work — otherwise it double-counts effort.
6. **Multi Language is listed as Enterprise-tier**, but Bangla/English
   toggle already exists today (`resources/lang/bn.json`, `LanguageController`)
   as a base-tier feature. Enterprise-tier should mean "beyond bn/en" or
   "per-tenant configurable default," not the base toggle.
7. **Sub Categories** (Menu Management) is cheaper than it looks — `categories`
   already has a `parent_id` column (added for exactly this, unused so far).
   Mostly a UI job, not a schema job.
8. **Offline POS** sits in Long-Term Vision only. Given Bangladesh
   power/internet reliability, this is worth reconsidering earlier than
   "long-term" — flagging as a risk, not forcing a reorder.

---

## Tenancy & Billing (built 2026-08-05)

- [x] `Tenant`, `Plan`, `Subscription` models — `plans` seeded (Free Trial/Basic/Pro/Enterprise) via `PlanSeeder`
- [x] Registration flow (`RegisteredUserController`) — creates Tenant + owner User (Admin role) + first Branch (`MAIN`/Outlet) + 30-day trial Subscription + tenant defaults (departments, designations, leave types, chart of accounts, units, categories, brands, attributes, walk-in/supplier parties, hero image, about/mission copy) in one DB transaction. Deliberately skips `ProductSeeder`/`BranchSeeder` — a real tenant builds its own menu and only gets the one auto-created branch.
- [x] `tenant_id` added directly to: `users`, `branches`, `company_settings`, `categories`, `brands`, `units`, `attributes`, `products`, `parties`, `departments`, `designations`, `leave_types`, `ledger_accounts`, `ledger_transactions`. Everything else (`stock_movements`, `employees`, `payroll_runs`, `projects`, `product_variants`, ...) inherits isolation transitively through its (non-nullable) `branch_id` → `Branch.tenant_id`.
- [x] `TenantScope` + `BelongsToTenant` trait — mirrors `BranchScope`/`BelongsToBranch`. **Important exception: `User` does NOT use this** — a global scope on the Authenticatable model itself that calls `auth()->check()` inside `apply()` recurses forever (resolving the session user queries User → scope fires → checks auth → needs the user resolved → queries User again). `Admin\UserController` filters `tenant_id` explicitly instead; route-bound `{user}` params are guarded with `guardSameTenant()`.
- [x] `CompanySetting::current(?int $tenantId = null)` — no longer a hardcoded `id=1` singleton; falls back to `auth()->user()?->tenant_id`, and returns an unsaved default instance (nothing to persist) when there's no tenant context at all (anonymous visitor).
- [x] Branch-limit enforcement — `BranchController::create()`/`store()` check `Tenant::canCreateAnotherBranch()` against the current plan's `max_branches`.
- [x] `EnsureSubscriptionActive` middleware (`subscription-active` alias) — redirects to `/admin/billing` once trial/subscription lapses; exempts `billing.*` and `logout` so a locked-out tenant can still reach the page that unlocks them.
- [x] `Admin\BillingController` + `admin/billing/index` view — shows current package + branch usage, lets the tenant switch plans. **No real payment gateway wired up** — `store()` activates the chosen plan immediately (a month from today) so the rest of the app has something real to check; swap that one method for an actual gateway/webhook flow later.
- [x] Root `/` now serves a dedicated SaaS marketing page (`website/saas-home.blade.php`) — hero, feature highlights, live Pricing from `Plan`, Register CTA. `WebsiteController::about/media/career/contact` still serve the *old* single-tenant company-site content — see follow-ups below.

### Follow-ups this pass surfaced (not yet done)

- [ ] **Cross-tenant ID guessing via `exists:` validation rules.** Laravel's `exists:table,column` rule queries the raw table, bypassing Eloquent global scopes entirely — so e.g. `'branch_id' => ['exists:branches,id']` in `EmployeeController` currently accepts *any* tenant's branch id, not just the acting user's own. This is systemic (every `exists:categories,id` / `exists:brands,id` / `exists:products,id` / etc. across the admin controllers), not a one-file fix — needs its own audit pass before onboarding real competing tenants.
- [ ] **`product_variants.sku`/`barcode` are still globally unique**, no `tenant_id` on that table. Not an active bug today (only the demo tenant seeds variant products; real registrations start with an empty menu), but two real tenants both picking the same variant SKU suffix convention will collide. Needs either a direct `tenant_id` column or scoping the unique index through `product_id`.
- [ ] **Per-tenant public website (`WebsiteController::about/media/career/contact`) still queries `Product`/`Category`/`Branch` unscoped** for an anonymous visitor — fine with one tenant in the database, a real cross-tenant data leak once a second one exists. Needs real subdomain-per-tenant routing (or an explicit tenant-by-domain resolver) before launch; `home()` was fixed this pass because it no longer needs any tenant's business data at all.
- [ ] Payment gateway integration (SSLCommerz/bKash) for `BillingController::store()` — currently trust-activates the plan with no payment collected.

---

## MVP (Version 1.0)

### Authentication & Security
- [x] Login / Logout / Password Reset / Profile Management (Breeze)
- [x] User Management, Role & Permission Management (Spatie)
- [x] Activity Logs (`AuditLog` + `Auditable` trait)
- [ ] Register Restaurant (tenant onboarding — see Tenancy & Billing above)

### Dashboard
- [x] Revenue/Expense/Profit summary widgets exist for the accounting side (`DashboardController`)
- [ ] Sales Summary, Today's/Running/Kitchen Orders, Stock Alerts, Top Selling Items — all depend on POS/Kitchen existing first

### Branch Management
- [x] Branch Management (renamed from Site — Head Office/Outlet/Central Kitchen/Cloud Kitchen/Warehouse types)
- [ ] Floor Management, Table Management, Counter Management
- [x] Branch Settings (Company/Website/Attendance settings exist; per-branch settings do not)

### POS
- [ ] Everything — no POS exists in this branch (was stripped from the generic base; see `README.md` §0). Historical `PurchaseController`/ledger-posting pattern in `docs/accounting-foundation.md` is the closest existing precedent to follow for how Sale should post to the ledger.

### Menu Management
- [x] Categories (reseeded for restaurant: Appetizers, Main Course, Rice & Biryani, etc.)
- [ ] Sub Categories (schema-ready via `parent_id`, no UI yet)
- [x] Food Items (`Product`, reseeded as menu items)
- [x] Variants (`Attribute`/`AttributeValue`/`ProductVariant`, reseeded as Portion Size + Spice Level)
- [ ] Add-ons, Combo Meals, Happy Hour Pricing
- [x] Food Images (`Product.image_path`, `product_images` — schema exists, no seeded photos)
- [x] Availability (`Product.status` toggle)

### Kitchen Management
- [ ] Everything (KDS, order queue, cooking timer, kitchen notes) — depends on POS/Orders existing first

### Inventory Management
- [x] Ingredients (`Product`), Units, Suppliers (`Party.is_supplier`)
- [ ] Purchase, Purchase Return (existed pre-strip-down per `docs/accounting-foundation.md`, not in current tree)
- [x] Stock Adjustment, Stock Transfer
- [~] Stock Alerts (`reorder_level` field exists, no alert UI/notification)
- [~] Waste Management (`damage_expiry` stock movement type exists, no dedicated screen)

### Recipe Management
- [ ] Everything — no BOM/recipe linking a dish to its ingredients yet. `Product` is currently sold directly against its own stock; a real kitchen needs dish → ingredient-quantity mapping before stock deducts correctly.

### Customer Management
- [x] Customer Database (`Party.is_customer`)
- [ ] Customer History (depends on Sales existing), Loyalty Points, Membership, Wallet, Birthday Offers

### Employee Management
- [x] Employee Profile, Attendance, Salary (`SalaryStructure`, `PayrollRun`)
- [~] Shift Management (single default shift start time in Attendance Settings, no per-employee/multi-shift)
- [ ] Performance

### Expense Management
- [x] Expense Categories (`LedgerAccount` income_expense group), Daily Expenses
- [ ] Recurring Expenses

### Basic Accounting
- [x] Cash Book (Day Book), Bank Accounts, Income, Expense, Ledger — already **beyond** "basic": Trial Balance, Balance Sheet, and P&L also exist.

### Reports
- [x] Stock Report, Due Report, Profit & Loss, product transaction history
- [ ] Sales/Purchase/Kitchen/Tax reports (depend on those modules existing)

---

## Version 1.5

- [ ] Online Ordering (website ordering, pickup, delivery, reservation, online payments)
- [ ] QR Table Ordering (full sub-list — QR per table, scan-to-menu, cart, order tracking, call waiter, request bill, auto-detect branch/floor/table/source)
- [ ] Mobile Applications (Customer, Waiter, Kitchen, Owner apps)
- [ ] At least one payment gateway (see Review note #4)

## Version 2.0

- [ ] CRM (SMS/WhatsApp/Email marketing, coupons, referral, segmentation)
- [ ] Warehouse (multiple warehouse, batch, expiry, warehouse transfer)
- [ ] Procurement (purchase requests, approval, supplier comparison)
- [ ] HRM — **Payroll already exists in MVP** (`PayrollRun`); this phase is really just Leave Management, Holiday Management, Employee Documents
- [ ] Delivery Management (rider, live tracking, delivery proof)

## AI Features (see Review note #5 re: overlap with "Magical Features")

- [ ] AI Business Copilot (daily summary, natural-language reports, insights, profit suggestions)
- [ ] AI Inventory (prediction, purchase suggestions, low-stock/expiry prediction)
- [ ] AI Kitchen (cooking priority, load balancing, prep suggestions)
- [ ] AI Pricing (dynamic pricing, combo/promotion suggestions)
- [ ] AI Customer (segmentation, behavior analysis, LTV, retention prediction)
- [ ] AI Sales (sales/revenue/profit forecast)
- [ ] AI Fraud Detection (fake discounts, suspicious refunds, cash fraud)
- [ ] AI Reviews (Google/Facebook/food-delivery review analysis, sentiment)

## Future Features

- [ ] AI Customer Prediction (hourly → seasonal/festival/weather-based)
- [ ] Smart Staff Planning, Smart Ingredient Planning
- [ ] Franchise Management (multiple companies, franchise dashboard, central kitchen, branch comparison) — note: this overlaps with Tenancy & Billing above; franchise = multiple Tenants under one owner, worth designing together rather than twice
- [ ] API & Integrations (REST API, webhooks, payment/SMS/WhatsApp/email/accounting/food-delivery-platform integrations)

## Enterprise Features

- [x] Audit Logs (already in MVP, done)
- [ ] Approval Workflow, API Access, Custom Roles, Data Backup/Restore, Multi Currency, White Label, Plugin Marketplace
- [~] Multi Language — bn/en toggle already exists at base tier (see Review note #6); Enterprise scope is "beyond bn/en"

## Magical Features (USP)

Marketing framing over a subset of "AI Features" + a few standalone ideas
(Voice Order Taking, Voice Kitchen Commands, Digital Twin Dashboard, QR Smart
Ordering, Natural Language ERP Search). Don't plan these as separate work
from the AI Features section above — pull from there when the time comes.

## Long-Term Vision

Cloud-native, API-first, mobile-first, offline POS, plugin ecosystem,
multi-country, multi-brand. No action items yet — revisit once MVP + v1.5
are live and there's real usage data to prioritize from.

---

## Suggested build order (high-level, not a schedule)

1. Tenancy & Billing (registration, plan, trial/subscription, branch limits)
2. Menu Management gaps (sub-categories, add-ons, combo meals) — cheap, unblocks POS
3. Recipe/BOM — needed before POS can deduct stock correctly per sale
4. POS core (dine-in + takeaway first, delivery after; one payment method before multiple; print before reprint/refund)
5. Kitchen Display System (depends on POS orders existing)
6. Floor/Table/Counter Management (depends on POS dine-in flow)
7. Reports that depend on POS (Sales, Kitchen, Tax)
8. Everything else in v1.0 (Loyalty/Wallet/Membership, Recurring Expenses, Performance reviews) as fast-follow
9. v1.5 (Online Ordering, QR Ordering, mobile apps) once v1.0 is stable in production
