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

### Phase 1 Deliverable — Screen Inventory & Implementation Checklist

**Frontend source:** `skoolyst-blog-management-system` UI export (bolt.new build, Supabase-backed).

#### Public site screens
- [ ] `index.php` → Home — hero/search, featured + latest post grids, newsletter form
- [ ] `blog.php` → Archive — search, category filter, sort, pagination
- [ ] `category.php` → Category archive — title/description driven by `?cat=` slug
- [ ] `post.php` → Single post — cover, body, tags, author block, share buttons, comments + comment form
- [ ] `about.php` → Static content + team grid (bug: reads undefined global `MOCK_AUTHORS`)
- [ ] `contact.php` → Contact form (currently client-side only, "demo — no real submission")

#### Dashboard screens (auth-gated)
- [ ] `dashboard/login.php` → Login (Supabase auth today; demo credentials hard-coded in markup)
- [ ] `dashboard/index.php` → Overview — stat cards, monthly views chart, recent posts table
- [ ] `dashboard/posts.php` → Posts list/manage (bug: reads undefined global `MOCK_CATEGORIES`)
- [ ] `dashboard/post-editor.php` → Create/edit post form (title, slug, cover, category, tags, status)
- [ ] `dashboard/categories.php` → Categories CRUD (modal-driven)
- [ ] `dashboard/media.php` → Media library

#### Shared assets
- `assets/css/style.css`, `assets/css/dashboard.css` — design tokens & styles
- `assets/js/api.js` — full Supabase REST/Auth client (browser talks to Supabase directly)
- `assets/js/auth.js`, `assets/js/app.js`, `assets/js/dashboard.js` — page logic
- `supabase/migrations/*.sql` — existing schema (`blog_users`, `blog_categories`, `blog_posts`, `blog_tags`, `blog_post_tags`, `blog_comments`, `blog_media`, `blog_post_views_daily`, `blog_audit_log`) — useful reference for the module's own MySQL schema in Phase 5

#### Mapping: frontend screen → blueprint MVC

| Frontend screen | Target view | Layout | Controller | Notes |
|---|---|---|---|---|
| index.php | resources/views/frontend/home.php | frontend | PostController@home | stub exists |
| blog.php | resources/views/frontend/blog.php (new) | frontend | PostController@index (new) | |
| category.php | resources/views/frontend/category.php (new) | frontend | CategoryController@show (new) | |
| post.php | resources/views/frontend/post.php (new) | frontend | PostController@show (new) | |
| about.php | resources/views/frontend/about.php (new) | frontend | PageController@about (new) | team grid needs real Author data |
| contact.php | resources/views/frontend/contact.php (new) | frontend | ContactController@store (new) | needs real server-side handling |
| dashboard/login.php | resources/views/auth/login.php | auth | AuthController@login | stub exists |
| dashboard/index.php | resources/views/admin/dashboard.php | admin | DashboardController@index | stub exists |
| dashboard/posts.php | resources/views/admin/posts/index.php (new) | admin | PostAdminController@index (new) | |
| dashboard/post-editor.php | resources/views/admin/posts/edit.php (new) | admin | PostAdminController@edit/update (new) | |
| dashboard/categories.php | resources/views/admin/categories/index.php (new) | admin | CategoryAdminController@index (new) | |
| dashboard/media.php | resources/views/admin/media/index.php (new) | admin | MediaController@index (new) | |

#### Missing / inconsistent parts found in the source ZIP
- [ ] `api.js` talks directly to Supabase from the browser (URL + anon key hard-coded) — must be fully replaced by our own PHP session-auth + `routes/api.php` endpoints; none of its Supabase calls carry over as-is.
- [ ] Three leftover references to an undefined global from an earlier mock-data version: `MOCK_CATEGORIES` (blog.php, dashboard/posts.php) and `MOCK_AUTHORS` (about.php) — throws JS errors as shipped.
- [ ] Contact form and comment form are demo-only (no real submission) — need real Controllers/Services.
- [ ] Color tokens partially match the blueprint theme (`--primary:#0f4077` = blueprint Blue) but `--secondary:#4361ee` is not the blueprint's Neon Cyan/Gold — needs remapping in Phase 3.
- [ ] No CSRF protection anywhere (expected for a static prototype) — required per blueprint rules.
- [ ] Login page hard-codes demo credentials in the markup — remove once real auth is wired up.

## Phase 2 — Core Setup

* Create the blueprint directory structure.
* Configure `.env`, Composer/autoloading and configuration.
* Set up Core classes.
* Set up database connection.
* Set up Router, Request, Response, View and Session.
* Verify the application boots correctly.

**CHECKPOINT → STOP AND REPORT**

### Phase 2 Deliverable — Core Setup Report

**Status:** PASS

**Completed:**
- [x] `app/Core/Router.php` implemented: static + `{param}` dynamic segments, per-route middleware chain (resolved to `Skoolyst\Middleware\{Name}Middleware::handle()`), closure and `[Controller::class, 'method']` handlers, JSON/HTML-aware 404 fallback
- [x] `app/Core/Request.php` extended: `_method` override (PUT/PATCH/DELETE from HTML forms), JSON body parsing (`jsonBody()`), `wantsJson()`, `query()`, `only()`
- [x] `app/Core/Response.php` extended: `text()` and `html()` alongside existing `json()`/`redirect()`
- [x] `app/Core/Session.php` implemented: cookie params from `.env` (`SESSION_NAME`/`SESSION_SECURE`/`SESSION_HTTP_ONLY`), `get`/`put`/`has`/`forget`/`regenerate`/`destroy`
- [x] `app/Core/Validator.php` implemented: `required`, `email`, `numeric`, `min`, `max`, `confirmed`, `in` rules, pipe-separated rule strings
- [x] `app/Middleware/{Auth,Guest,Admin,Api}Middleware.php` given a real `handle(array $params): bool` contract so the Router can invoke them; **Auth/Guest** already redirect based on `is_authenticated()`; **Admin**'s role check and **Api**'s token/rate-limit checks are left as explicit `TODO`s for Phase 4/8 since that's where the real auth logic belongs
- [x] `routes/web.php` now returns a wired `Router` instance with a boot-verification route
- [x] `public/index.php` now calls `$router->dispatch(Request::method(), Request::uri())`
- [x] `app/Core/Database.php` (PDO connection) and `app/Core/View.php` reviewed — already correct for this phase, left as-is
- [x] Boot verified end-to-end with PHP 8.3's built-in server: `GET /` → `200 OK` with session cookie set; unmatched route → `404` via `resources/views/errors/404.php`; a dynamic `{param}` route and the new `Validator` rules were also exercised directly — all passed

**Files changed:**
- `app/Core/Router.php`, `app/Core/Request.php`, `app/Core/Response.php`, `app/Core/Session.php`, `app/Core/Validator.php`
- `app/Middleware/AuthMiddleware.php`, `app/Middleware/GuestMiddleware.php`, `app/Middleware/AdminMiddleware.php`, `app/Middleware/ApiMiddleware.php`
- `routes/web.php`, `public/index.php`

**Assumptions:**
- `composer install` could not be run in this environment (no access to packagist/getcomposer here), so boot testing used a temporary local PSR-4 autoload shim instead of `vendor/autoload.php` — the shim was never committed (`vendor/` is already gitignored). Composer's own `require` (`vlucas/phpdotenv`) is unchanged; run `composer install` on your machine/server before testing there.
- No `.env` was created/committed; defaults baked into the Core classes (from `.env.example`) were used for the boot test. Create your own `.env` from `.env.example` for real DB-backed testing.
- `app/Core/Model.php`'s active-record methods (`find`, `all`, `save`, etc.) were intentionally left for Phase 5 (Database & Models), per the blueprint's own phase split.

**Remaining:**
- [ ] Phase 3 — Shared UI System (reusable components, layouts, theme, responsiveness)

**Next Phase (Phase 3 — Shared UI System):**
Convert the ZIP's UI into reusable PHP components (header/navbar, footer, sidebar), build the frontend/admin/auth layouts, and remap the ZIP's colors onto the Skoolyst theme tokens identified in the Phase 1 report.

**STOPPED — Waiting for your approval to continue.**

## Phase 3 — Shared UI System

* Convert existing UI into reusable PHP components.
* Implement header/navbar, footer and sidebar.
* Implement layouts for frontend, admin and authentication.
* Implement buttons, cards, tables, forms, inputs, alerts, modals, pagination, badges and empty states.
* Apply the shared Skoolyst theme.
* Ensure mobile/tablet/desktop responsiveness.

**CHECKPOINT → STOP AND REPORT**

### Phase 3 Deliverable — Shared UI System Report

**Status:** PASS

**Completed:**
- [x] Structural components implemented: `navbar.php`, `footer.php` (new), `sidebar.php` — theme-matched, using `is_authenticated()`/`auth_user()` for conditional nav state
- [x] Atomic reusable components implemented via a new `component($name, $data)` helper: `button`, `card`, `table` (with empty-state fallback), `input` (text/email/password/textarea/select + inline errors), `alert` (success/error/warning/info, dismissible), `modal`, `pagination`, `badge`, `empty-state`
- [x] `View::render()` extended with an optional `$layout` param — captures the view's output as `$content` and wraps it, so Controllers/routes call e.g. `View::render('frontend/home', [], 'frontend')`
- [x] Layouts implemented: `frontend.php` (navbar + flash alerts + footer), `admin.php` (sidebar + topbar + flash alerts), `auth.php` (centered card) — all three pull in `assets/js/app.js`, admin also pulls `admin.js`
- [x] `head.php` extended with optional `$description` and `$extraCss`
- [x] Existing stub views converted into real, component-driven pages so all three layouts could be exercised end-to-end: `frontend/home.php` (hero/search/featured grid/newsletter), `admin/dashboard.php` (stat cards + posts table), `auth/login.php`, `errors/404.php`, `errors/500.php` — all using **static placeholder data**; real data wiring is Phase 6/7's job
- [x] Skoolyst theme (Navy/Blue/Cyan/Gold tokens already in `app.css`) applied across every new component and layout; `admin.css` extended with the sidebar/topbar shell
- [x] Responsive rules added: collapsing navbar → hamburger menu below 768px, collapsing admin sidebar → toggle below 900px, `post-grid`/`stat-grid` auto-fit grids, existing `table-wrap` horizontal scroll reused
- [x] `resources/js/app.js` (nav toggle, dismissible alerts, modal open/close) and `resources/js/admin.js` (sidebar toggle, `data-confirm` guard) implemented
- [x] `public/assets/{css,js}` populated as the web-servable mirror of `resources/{css,js}` (the architecture keeps `public/` as the only web-exposed directory — see Assumptions)
- [x] Added `bin/dev-router.php`, a local-testing-only router for `php -S` that mirrors `public/.htaccess`'s "serve real files directly, else route through `index.php`" rule (same pattern already used in the sibling [[skoolyst-blogs]] repo)
- [x] Boot-tested all three layouts end-to-end via `php -S ... bin/dev-router.php`: `/` (frontend, 200), `/login` (auth, 200), `/dashboard` unauthenticated (redirects 302 to `/login` via `AuthMiddleware`) and authenticated (rendered directly, 200, no errors), static assets under `/assets/...` (200), unmatched route (404 via the themed error page) — no PHP warnings/errors in any case

**Files changed:**
- New: `resources/views/components/footer.php`, `app/Helpers/component.php`, `bin/dev-router.php`, `public/assets/css/{app,admin}.css`, `public/assets/js/{app,admin,module}.js`
- Rewritten: all 9 previously-empty component stubs, all 3 layouts, `resources/views/{frontend/home,admin/dashboard,auth/login,errors/404,errors/500}.php`, `resources/css/{app,admin}.css`, `resources/js/{app,admin}.js`
- Modified: `app/Core/View.php` (layout support), `app/Core/Router.php` (404 now uses the frontend layout), `routes/web.php` (temporary closures to exercise the 3 layouts — replaced by real Controllers in Phase 6), `bootstrap/helpers.php` (registers `component()`)

**Assumptions:**
- The ZIP's actual page copy/markup (hero text, dashboard stats, etc.) was **not** ported verbatim — Phase 3 is structural (components/layouts/theme), so placeholder copy was used; Phase 7 (Views & Functionality) is where each screen gets its real content and is wired to live data.
- `resources/css`/`resources/js` are the source files to edit; `public/assets/css`/`public/assets/js` are their served copies (no build step exists in this blueprint). Keep both in sync when editing — worth automating in Phase 11 (Documentation) or earlier if this becomes error-prone.
- Same sandbox limitation as Phase 2: no `composer install` here, boot-tested via a local, uncommitted autoload shim.

**Remaining:**
- [ ] Phase 4 — Authentication & Security (real login/logout, password hashing, CSRF verification, role checks in `AdminMiddleware`)

**Next Phase (Phase 4 — Authentication & Security):**
Wire `AuthController` + `AuthService` to real session-based login/logout against a `users` table, verify CSRF tokens on state-changing requests, and fill in the `AdminMiddleware`/`ApiMiddleware` TODOs left in Phase 2.

**STOPPED — Waiting for your approval to continue.**

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
