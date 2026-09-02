# Skoolyst Module — Core PHP MVC Blueprint

Reusable base architecture for independent Skoolyst modules such as Blog, Store, Media, MCQs, Ads, etc.

## Rules
- PHP MVC; views are `.php`, never `.html`.
- Every module uses the same directory structure.
- Keep controllers thin; business logic belongs in Services.
- Use Models for database access/domain persistence.
- Shared authentication, helpers, middleware, components and design tokens must remain consistent.
- APIs live in `routes/api.php`; prefer versioning such as `/api/v1`.
- Never commit `.env`, uploads, logs, cache or temporary files.
- Each module can have its own database; avoid cross-module DB joins.
- Use PDO with prepared statements.
- Validate all input server-side; escape output in views.
- Use CSRF for browser forms and appropriate authentication for APIs.
- Keep module-specific CSS/JS minimal and based on the shared design system.

## Theme
Skoolyst Navy `#0A0E2A`, Blue `#0F4077`, Neon Cyan `#00D9FF`, Gold/Amber `#F4B942`, white surfaces and neutral text.

## Suggested module naming
`skoolyst-blog`, `skoolyst-store`, `skoolyst-media`, `skoolyst-mcqs`.

## Development
1. Copy this blueprint.
2. Rename project and `.env`.
3. Create module database.
4. Run `composer install`.
5. Implement module Controllers/Models/Services.
6. Add routes.
7. Build pages from shared components.
8. Add tests.
9. Configure the web server document root to `public/`.





# Project Development Instructions

## Source

* **[REPOSITORY URL]** = Skoolyst Core PHP MVC project blueprint. Follow its directory structure, architecture, naming conventions and shared design system.
* **[UPLOADED ZIP]** = This project's existing frontend application. Analyze and adapt it into the blueprint architecture.

## Goal

Convert the uploaded frontend into a complete **Core PHP MVC Skoolyst module** using the repository blueprint.

Do not redesign unnecessarily. Preserve the existing UI/UX where possible, but make it fully responsive, structured, reusable and production-ready.

## Rules

* Follow the repository blueprint exactly.
* Views must be `.php`, never `.html`.
* Use Controllers → Services → Models architecture.
* Use shared Components for buttons, cards, tables, inputs, modals, alerts, navbar, sidebar, footer, pagination, etc.
* Apply the shared Skoolyst theme/design system consistently.
* Keep frontend, admin and authentication layouts separate.
* Implement proper routing, middleware, validation, authentication and API structure.
* Avoid duplicate code.
* Keep module-specific code inside the module.
* Do not add unnecessary frameworks or dependencies.
* Do not modify completed work without a reason.

# Development Phases

Work **one phase at a time**. Complete, test and report the current phase before starting the next phase.

## Mandatory Stop-and-Report Checkpoints

**STOP means STOP. Do not continue automatically.**

At the end of **every phase**:

1. Stop all implementation work.
2. Run the relevant checks/tests for that phase.
3. Review the files changed or created.
4. Update the task checklist with `[x]` and `[ ]`.
5. Report:

   * What was completed
   * Files/folders created or changed
   * Tests/checks performed
   * Any errors or warnings
   * Any assumptions made
   * What remains
   * What the next phase will do
6. **Wait for explicit user approval before starting the next phase.**

Use this checkpoint format:

```text
=== PHASE X COMPLETE ===

Status: PASS / PASS WITH WARNINGS / BLOCKED

Completed:
- [x] ...
- [x] ...

Files changed:
- ...

Tests / Checks:
- ...

Warnings / Issues:
- ...

Assumptions:
- ...

Remaining:
- [ ] ...

Next Phase:
- ...

STOPPED — Waiting for approval to continue.
```

### Approval Rule

Do **not** interpret silence, a previous instruction, or the existence of remaining tasks as approval.

Only continue when the user explicitly says something such as:

* `continue`
* `next phase`
* `approved`
* `start phase 2`
* `proceed`

If the user requests changes at a checkpoint, fix those changes **before moving to the next phase**.

If a phase is **BLOCKED**, stop immediately and explain exactly what is blocking progress. Do not work around important architectural decisions without user approval.

---

## Phase 1 — Analyze & Plan

* Inspect the repository blueprint.
* Inspect the uploaded frontend ZIP.
* Understand all existing screens, components, pages and functionality.
* Map frontend screens to MVC structure.
* Identify missing/inconsistent parts.
* Do not implement yet.
* Update this README with the implementation checklist.

**CHECKPOINT → STOP AND REPORT**

## Phase 2 — Core Setup

* Create the blueprint directory structure.
* Configure `.env`, Composer/autoloading and configuration.
* Set up Core classes.
* Set up database connection.
* Set up Router, Request, Response, View and Session.
* Verify the application boots correctly.

**CHECKPOINT → STOP AND REPORT**

## Phase 3 — Shared UI System

* Convert existing UI into reusable PHP components.
* Implement header/navbar, footer and sidebar.
* Implement layouts for frontend, admin and authentication.
* Implement buttons, cards, tables, forms, inputs, alerts, modals, pagination, badges and empty states.
* Apply the shared Skoolyst theme.
* Ensure mobile/tablet/desktop responsiveness.

**CHECKPOINT → STOP AND REPORT**

## Phase 4 — Authentication & Security

* Implement authentication.
* Add Auth/Admin/Guest/API middleware.
* Add CSRF protection.
* Add server-side validation.
* Add secure session handling.
* Apply proper authorization.

**CHECKPOINT → STOP AND REPORT**

## Phase 5 — Database & Models

* Create module database structure.
* Add migrations/SQL.
* Create Models.
* Keep database logic out of Controllers and Views.

**CHECKPOINT → STOP AND REPORT**

## Phase 6 — Services & Controllers

* Create module Services.
* Create Controllers.
* Move business logic into Services.
* Connect Controllers → Services → Models.
* Keep Controllers thin.

**CHECKPOINT → STOP AND REPORT**

## Phase 7 — Views & Functionality

* Convert all frontend screens to PHP Views.
* Connect real data.
* Implement forms, CRUD, search, filters, pagination and required interactions.
* Complete admin and frontend functionality.

**CHECKPOINT → STOP AND REPORT**

## Phase 8 — API

* Implement API routes under `routes/api.php`.
* Use consistent JSON responses.
* Add API middleware/authentication where required.
* Validate all API input.

**CHECKPOINT → STOP AND REPORT**

## Phase 9 — Responsive & UI QA

* Test every screen on mobile, tablet and desktop.
* Fix layout overflow, spacing, typography, navigation and component issues.
* Ensure header/footer/sidebar work correctly everywhere.

**CHECKPOINT → STOP AND REPORT**

## Phase 10 — Final QA

* Test all routes.
* Test authentication/authorization.
* Test CRUD and forms.
* Test validation and security.
* Test API endpoints.
* Check PHP errors and broken links.
* Remove duplicate/dead code.
* Confirm blueprint structure is followed.

**CHECKPOINT → STOP AND REPORT**

## Phase 11 — Documentation

* Update README.
* Document setup/install steps.
* Document database configuration.
* Document routes/API.
* Document important module components and services.
* Mark every completed task with `[x]`.

**FINAL CHECKPOINT → STOP AND REPORT FINAL STATUS**

### Task Tracking Rule

Use this format:

* [ ] Task
* [x] Completed task

**Important:** Never work on future phases while the current phase is incomplete. Never automatically proceed after a checkpoint. Always stop and wait for explicit approval.
