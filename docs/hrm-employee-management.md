# HRM / Employee Management — Feature Levels (Level 1 / Level 2 / Level 3)

Expands **Module 8 — HRM** (and its touchpoints with Module 4 — Contacts/Employee
profile, and Module 7 — Task Management) from the one-paragraph bullet list in
`README.md` into a build-order feature ladder, the way an industry-grade HR
module is usually staged: **Core master + attendance + basic pay → full
payroll + performance + self-service → enterprise HR (recruitment, compliance,
analytics, multi-entity)**.

Same rules as every other module in this ERP apply here: everything below is
**tenant feature-flagged** (`tenant_features`), a small trading client can run
Level 1 only and never see Level 2/3 menus; RBAC + audit log + approval
workflow (maker–checker) are cross-cutting, not re-specified per level; every
salary/incentive/loan posting goes through `LedgerService::post()`, never a
manual balance field — same pattern as `accounting-foundation.md`.

## Decision: Employee vs. Party

`Party` (Module 4) is the model behind **Customer** and **Supplier** — its
whole design is built around one thing: *is this entity on the other side of
an Accounts Receivable/Payable ledger line?* (`is_customer`/`is_supplier`,
`opening_balance` → `postOpeningBalanceToLedger()`, `receivableBalance()`/
`payableBalance()` reading `ledger_transaction_lines.party_id`). It has **no
relationship to `User`/login/RBAC at all** — it's a pure business-contact
record, not an identity.

An **Employee is a different kind of thing**: it's internal staff, and it
*optionally* needs a login identity (`User`, which already carries Spatie
roles + site assignment). Reusing `Party` for Employee would mean either:

- bolting attendance/payroll/roster fields onto a model whose job is AR/AP
  bookkeeping (scope creep on a model other code already depends on being
  "just" Customer/Supplier), **and**
- still having to invent the User-link and login-toggle from scratch anyway,
  since Party doesn't have it today — so reusing Party buys nothing on the
  one piece (login) that actually matters here.

**Recommendation: a dedicated `Employee` model**, separate from `Party`,
with a **nullable `user_id`** and an explicit **"Enable Login" toggle** (see
§1.1). This is the same pattern most mature HR modules use (e.g. Odoo's
`hr.employee` ↔ `res.users`, ERPNext's Employee ↔ User) — Employee and User
are related but not identical, because plenty of employees (factory floor,
contract/temporary workers logged via Module 7's lightweight "worker" party)
never need to log in at all, while others (HR, Manager, Accountant, Sales)
clearly need both a business profile *and* a login.

**Follow-on consequence for Level 2 (Loan/Advance):** since Employee isn't a
Party, `ledger_transaction_lines.party_id` (which only makes sense for
Customer/Supplier control accounts) doesn't cover Employee Advance/Salary
Payable postings. The clean fix is the same trick applied to a different
foreign key: add a nullable `employee_id` column to `ledger_transaction_lines`
(mirroring `party_id`), and add an `employee` account group alongside the 5
existing groups in `accounting-foundation.md` for "Employee Advance
Receivable" / "Salary Payable" control accounts. No change needed to
`LedgerService::post()`'s core contract — just one more optional stamped ID,
exactly like `party_id` already is.

---

## Level 1 — Foundation (every client that runs payroll needs this)

Goal: replace the Excel/paper *hajira khata* and manual salary sheet. This is
the bundle that ships in the existing roadmap's **Phase 2** alongside
Attendance + Payroll.

### 1.1 Employee Master

**Dedicated `Employee` model — not the `Party` model.** (See "Decision: Employee
vs. Party" below for the reasoning; this revises the earlier draft of this
doc, which had suggested reusing Party.)

- New `employees` table/model, independent of `Party`. Fields: name, phone,
  NID, photo, joining date, designation_id, department_id, employment type
  (`permanent`/`probation`/`contractual`/`part_time`), reporting_manager_id
  (self-FK), employment status (`active`/`resigned`/`terminated`/`on_leave`)
- `user_id` — **nullable**, one-to-one to the existing `User` model. Most
  factory-floor/contract staff never get a login; sales/managers/HR/accountants
  usually do.
- `enable_login` (boolean toggle on the Employee form):
  - **Turned on:** auto-provision a `User` row (username = phone or email,
    random temp password + forced reset on first login), assign a default
    `Employee` Spatie role (extending the existing RBAC roles: Owner, Manager,
    Accountant, Store-keeper, Sales, HR), link `employees.user_id`.
  - **Turned off:** never delete the linked `User` row (it may be
    `created_by`/`approved_by` on years of ledger/attendance/audit-log rows) —
    instead deactivate login only (a `users.is_active` flag gated in the auth
    guard), same "flag, don't delete" convention already used for
    `Party.status`, `Category.status`, etc.
  - Re-enabling later re-activates the same `User` row rather than creating a
    second one.
- Document store: NID copy, appointment letter, certificates (reuse the
  Attachment support already listed as a cross-cutting requirement)
- Department & Designation master (simple lookup tables, editable per tenant)
- Excel/CSV import of existing employee list (same "onboarding friction is the
  #1 silent killer" principle already called out for Parties/Products)

### 1.2 Attendance (basic)
- Manual attendance register (admin/HR marks present/absent/half-day/leave)
- Web check-in/check-out (single button, timestamp)
- Daily attendance sheet view + monthly summary per employee
- Late/absent auto-flag based on shift start time (single default shift only
  at this level — no roster engine yet)

### 1.3 Leave (basic)
- Fixed leave types (Casual, Sick, Earned — BD standard) with a yearly balance
  per employee
- Leave application → single-level approval (manager or admin)
- Leave balance auto-deducts on approval; balance visible to HR

### 1.4 Payroll (basic, non-negotiable minimum)
- Salary structure per employee: one flat monthly amount, or
  basic + house rent + medical + conveyance (BD convention) as separate fields
- Manual deduction line (absence days × per-day rate, advance recovery — entered,
  not rule-engine yet)
- Monthly **Salary Run**: generate → review list → approve → auto-post to
  Accounts as payment vouchers (`Dr Salary Expense / Cr Cash-Bank-MFS`, same
  `LedgerService::post()` entry point Purchase/Sales already use) → payslip PDF
- Payslip print/PDF + record of payment method (cash/bank/bKash/Nagad — reuses
  the MFS account types already modeled for Accounts)

### 1.5 Cross-cutting (Level 1 scope)
- RBAC roles: HR, Manager (own team only), Accountant (payroll approval),
  Employee (self, view-only where ESS is enabled)
- Audit log on every Employee/Attendance/Leave/Payroll create-update-delete
- Bangla + English labels for all of the above

**Key entities (Level 1):** `Employee` (own model, `user_id` nullable →
`User`), `Department`, `Designation`, `AttendanceLog`, `LeaveType`,
`LeaveBalance`, `LeaveRequest`, `SalaryStructure`, `PayrollRun`, `PayrollRunItem`

---

## Level 2 — Growth (what makes it a real HR system, not a salary sheet)

Goal: the client stops needing a separate biometric-vendor app and a separate
"who gets how much commission" spreadsheet. Matches the roadmap's **Phase 3 —
Stickiness** (incentives/performance, approval workflows).

### 2.1 Shift & Roster Management
- Multiple shift definitions (day/night/split), per-department or
  per-employee shift assignment
- Weekly roster planning (calendar view), roster publish + change log
- Overtime auto-calculation from actual vs shift hours (rule-configurable
  rate: 1x/1.5x/2x)

### 2.2 Attendance (upgraded)
- **Biometric device import** (ZKTeco CSV/API — common in BD factories),
  auto-matched to employee by device ID
- **Mobile check-in with GPS + selfie** for field/sales staff — ties into the
  same "field staff" use-case as Module 6 Delivery drivers
- Geo-fence option per site (flag "outside allowed radius" instead of hard
  blocking, since BD field connectivity is unreliable)
- Attendance regularization workflow: employee raises a correction request →
  manager approves (maker–checker, same pattern as purchase/salary approval)

### 2.3 Leave (upgraded)
- Configurable leave policy per employment type/tenure (e.g., probation
  employees get 0 earned leave)
- Leave calendar (team view, holiday calendar overlay)
- Multi-level approval when configured (manager → HR, for leaves above N days)
- Compensatory-off accrual from approved overtime/holiday work

### 2.4 Payroll (full engine)
- Rule-based salary components: earnings, deductions, employer contributions
  as separate configurable heads (not just 4 fixed fields)
- **Overtime, absence, and late-deduction rules** computed automatically from
  Attendance instead of manual entry
- **Employee advance/loan register** with auto installment deduction each
  salary run, balance tracking, early-settlement option
- **Festival bonus** (2x/year, BD standard) as a scheduled bonus run, prorated
  by tenure
- Payroll approval workflow (HR prepares → Accountant/Owner approves above a
  configurable threshold) before ledger posting — reuses the existing
  "maker–checker on salary runs" cross-cutting requirement
- Bulk payslip generation + bulk SMS/WhatsApp payslip notification

### 2.5 Incentive Engine
- Rule-based incentive sources feeding into the salary run: sales commission
  (from Module 2 `CommissionRule`), production piece-rate (from Module 1 BOM/
  Production Order), attendance bonus, task-completion bonus (from Module 7)
- Incentive preview before salary run finalization (so HR can sanity-check
  a commission spike before it posts)

### 2.6 Performance (basic)
- KPI scorecard per role: sales target %, attendance %, task completion %
  (pulled from Modules 2/7), entered manually where no source module exists
- Monthly rating + free-text appraisal note per employee
- Appraisal history visible on employee profile (12-month trend)

### 2.7 Employee Self-Service (ESS) — basic
- Employee login: view own payslip, leave balance, attendance log, apply for
  leave, submit attendance regularization request
- Manager view: team attendance/leave dashboard, approve requests inline

**Key entities (Level 2 additions):** `Shift`, `Roster`, `AttendanceRegularization`,
`SalaryComponent`, `EmployeeLoan`, `LoanInstallment`, `BonusRun`, `IncentiveRule`,
`IncentivePreview`, `KPIScore`, `Appraisal`

---

## Level 3 — Industry / Enterprise Grade

Goal: the module can stand on its own as "HRMS" for a client that has
outgrown a small-business setup (100+ employees, multiple branches/factories,
or compliance-sensitive sectors). This is roadmap **Phase 4+ / vertical
expansion** territory — build only once 3+ clients demonstrably need each
piece (same promotion rule as §7 of `README.md`).

### 3.1 Recruitment & Onboarding
- Job requisition → posting → candidate pipeline (applied/screened/interview/
  offer/hired) — lightweight ATS, not a full recruiting suite
- Offer letter generation from template, document checklist for joining
- Structured onboarding checklist (IT access, ID card, induction training)
  assigned as Module 7 tasks to relevant departments

### 3.2 Learning & Development
- Training calendar, session attendance, certification expiry tracking
  (relevant for factories with safety-certification requirements)
- Skill matrix per employee/department

### 3.3 Succession & Career Planning
- Designation ladder per department, promotion history on employee profile
- Successor tagging for key roles, readiness notes (qualitative, not
  algorithmic — this is a planning aid, not a prediction engine)

### 3.4 Disciplinary & Grievance Workflow
- Formal warning/show-cause letter issuance with document trail
- Grievance ticket (employee-raised, routed to HR, resolution log) —
  confidential visibility (only HR + named approver, enforced via RBAC)

### 3.5 Compliance & Statutory
- Bangladesh Labour Act–aligned leave entitlement templates (configurable,
  not hard-coded, since rules vary by sector/factory-vs-office)
- Provident Fund / Gratuity ledger tracking (employer + employee contribution,
  posts to Accounts as a distinct liability account)
- Statutory register exports (attendance, leave, wage register) in the format
  labour inspectors typically request
- Full & Final Settlement workflow on exit: leave encashment, loan
  clearance, gratuity/PF payout, asset return — single guided flow instead of
  manual multi-step closing

### 3.6 Multi-branch / Multi-entity HR
- Employee transfer between branches/sites with history log
- Branch-wise headcount, cost-center-wise payroll cost reporting
- Central HR policy with per-branch overrides (shift timing, leave calendar
  holidays) — same "core defaults, tenant/site overrides" pattern already used
  for pricing/discounts

### 3.7 Asset & Access Management
- Company asset assignment to employee (laptop, SIM, vehicle) with
  return-on-exit checklist tied into the F&F Settlement flow
- Access/permission audit trail tied to employment status change (auto-flag
  system access for review on resignation/termination)

### 3.8 Advanced Analytics & Self-Service
- HR dashboard: headcount trend, attrition rate, average tenure, overtime
  cost trend, department-wise salary cost %, absenteeism heatmap
- Manager Self-Service (MSS): team performance trend, leave/attendance
  approvals, budget-vs-actual headcount cost for their department
- Predictive flags (phase-2-of-Level-3, optional): attrition risk indicator
  from attendance/leave/performance pattern — feeds into Module 10 AI
  Assistant as a read-only insight, never an automated action

### 3.9 Integration & API
- HR events published on the core's existing webhook bus (`employee.checked_in`
  already listed in the architecture doc; add `employee.hired`,
  `employee.resigned`, `payroll.run_completed`, `leave.approved`)
- Payroll disbursement API hook for direct bKash/Nagad bulk-disbursement
  (beyond the manual MFS voucher entry of Level 1/2)
- Biometric/access-control device integration beyond CSV import (live API
  push, for factories with networked devices)

**Key entities (Level 3 additions):** `JobRequisition`, `Candidate`,
`OnboardingChecklist`, `TrainingSession`, `SkillMatrix`, `SuccessionPlan`,
`DisciplinaryCase`, `GrievanceTicket`, `StatutoryContribution` (PF/Gratuity),
`FinalSettlement`, `EmployeeTransfer`, `CompanyAsset`

---

## Feature-Level Summary Table

| Area | Level 1 (Foundation) | Level 2 (Growth) | Level 3 (Enterprise) |
|---|---|---|---|
| Employee Master | Profile + docs + import | Transfer/promotion history | Full lifecycle: hire → onboard → transfer → exit |
| Attendance | Manual + web check-in | Biometric import, GPS+selfie, roster/shift, regularization | Live device API, geo-fenced multi-site |
| Leave | Fixed types, single approval | Policy-driven, multi-level approval, comp-off | Statutory-aligned entitlement templates |
| Payroll | Flat/4-component salary, manual salary run | Full rule engine, OT/loan/bonus automation, approval workflow | PF/Gratuity, F&F settlement, bulk MFS disbursement API |
| Incentive | — | Commission/piece-rate/task-bonus engine | Budget-vs-actual cost analytics |
| Performance | — | KPI scorecard, monthly rating | Succession planning, skill matrix |
| Self-Service | — | ESS (view/apply), Manager team view | MSS with cost/headcount analytics |
| Recruitment | — | — | Requisition → pipeline → offer → onboarding |
| Compliance | — | — | Labour Act templates, statutory exports, F&F |
| Reporting | Attendance sheet, payslip | HR summary reports (Module 9) | Attrition, cost-center, predictive dashboards |

## Suggested Build Order (fits the existing 4-phase roadmap)

1. **Ship with Phase 2** (Attendance + Payroll, per `README.md` §5): Level 1
   in full, since that's the minimum any payroll-running client needs.
2. **Ship with Phase 3** (Stickiness): Level 2 in full — this is what turns
   "we recorded attendance" into "HR actually runs on this."
3. **Ship as vertical/enterprise expansion, demand-driven**: Level 3, one
   sub-feature at a time, applying the same ≥3-clients promotion rule as
   custom requests (§7 of `README.md`) — e.g. build Recruitment only once
   3+ clients ask for it, not speculatively.

## Notes / Open Decisions

- Level 3 compliance items (3.5) are Bangladesh-specific and should be
  reviewed with an actual HR/labour-law resource before being hard-coded as
  templates — ship them as **editable templates**, never fixed business logic,
  since labour rules differ by sector (factory vs. office vs. retail).
- Payroll and Accounts are already designed to share `LedgerService::post()`
  (see `accounting-foundation.md`) — no new posting mechanism needed, only
  new `type` values (`salary`, `bonus`, `loan_disbursement`, `loan_recovery`,
  `pf_contribution`) added to the existing fixed-const-array pattern
  (`LedgerTransaction::PREFIXES`/`TYPES`).
- Predictive attrition flags (3.8) should stay read-only/advisory, consistent
  with the AI Assistant's existing guardrail: "no financial advice, only
  their own data."
