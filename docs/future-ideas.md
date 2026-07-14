# Future Feature Ideas

A running backlog of feature ideas that are not being implemented yet. When a new idea
comes up, append a new entry below (don't edit/remove older entries unless they get
implemented or dropped). When an idea is picked up for implementation, move its entry
into a proper spec under `docs/superpowers/specs/` and mark it `Status: In Progress`
here.

---

## AI Auto Issue Resolver (production self-healing with admin approval)

- **Added:** 2026-07-14
- **Status:** Idea (not scoped, not started)

### Problem

Production (`abc.com`, i.e. `erp.vexasoft.net`) has no error monitoring today. When an
exception happens, nobody knows until a user reports it, and fixing it requires a
developer to reproduce, diagnose, and patch manually.

### Idea

Build a self-healing loop that uses AI to shrink the time between "exception happens in
production" and "verified fix is live," with a human admin approval gate before anything
ever touches production.

Rough flow as originally proposed:

1. An exception occurs on the live site. It's captured and stored as an error log entry
   with an `unresolved` / `unchecked` status, along with an AI-generated "most probable
   solution" note written at capture time.
2. Admin opens the error log dashboard and sees the list of unresolved errors with their
   AI-suggested solutions.
3. Admin clicks "Check" on an error → gets redirected into `staging.abc.com`, where the
   same scenario that caused the error is reproduced (originally proposed as cloning the
   full production DB into staging; see feasibility notes below for why this needs to be
   narrower).
4. Admin waits on the live site until staging is ready with the reproduced scenario, then
   moves to staging to verify.
5. Once staging reproduces the issue and a candidate fix is ready, admin reviews it on
   staging and clicks "Approve."
6. On approval, the fix is committed and pushed to GitHub, which triggers the existing
   `main` branch auto-deploy (`.github/workflows/deploy.yml`) to production.
7. Goal: shrink recurring/routine bug-fix turnaround without needing a developer in the
   loop for every incident.

### Feasibility notes (from initial discussion)

- **Error capture + AI suggestion** — straightforward, low risk. Standard pattern
  (comparable to Sentry-style error tracking) plus a Claude API call for root-cause /
  suggested-fix analysis. Good first phase, valuable standalone even without the rest.
- **Staging scenario replication** — cloning the *entire* production DB into staging per
  error is not practical: exposes customer PII into a second environment, is slow for a
  large DB, and adds ongoing storage/infra cost. A narrower approach (replaying just the
  specific request/job that triggered the error, with anonymized/sampled data) is more
  realistic and should be the actual design target.
- **Admin approval gate before deploy** — this is the right safety pattern and should not
  be weakened. AI-authored fixes should never auto-deploy without a human checking them
  on staging first.
- **AI auto-commit + push triggering the existing auto-deploy pipeline** — technically
  doable (Claude can generate a patch, commit, and push), but this is the highest-risk
  part of the system: a bad fix could go straight to production if review is rushed or
  the diagnosis is wrong. This does not eliminate the need for a developer — it changes
  the developer's role from "write the first draft" to "review and approve the AI's
  draft," which still matters most for architectural, security-sensitive, or complex
  business-logic bugs.

### Suggested decomposition when this gets picked up

Too large for a single spec/implementation pass. Natural sub-projects, in build order:

1. Error capture + storage + AI-suggested-solution note (dashboard, read-only, no
   automation of fixes or environments).
2. Admin review workflow UI (list, status, drill into an error).
3. Staging scenario replay (narrow, request/job-level — not full DB clone).
4. Approval-gated AI fix generation + commit/push into the existing deploy pipeline.

Each of these should go through its own brainstorming → spec → plan cycle when picked up.

---
