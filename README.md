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

## Deployment

Commands to run, in order, when standing this app up on a new server (fresh install) or pushing an update to an existing one.

### Requirements
- PHP 8.2+ with extensions: `pdo`, `pdo_mysql`, `mbstring`, `gd` (used for image uploads — cover images and the media library are converted to WebP through GD, so uploads fail without it).
- MySQL/MariaDB.
- Composer.
- Web server (Apache/Nginx) with the document root pointed at `public/`, `mod_rewrite` (or equivalent) enabled — see `public/.htaccess`.

### Fresh install
```bash
git clone <repo-url> && cd <repo-dir>

# 1. PHP dependencies
composer install --no-dev --optimize-autoloader

# 2. Environment config
cp .env.example .env
# edit .env: DB_HOST/DB_DATABASE/DB_USERNAME/DB_PASSWORD, APP_URL (full URL including /public
# if the vhost root isn't already public/), APP_ENV=production, APP_DEBUG=false

# 3. Create the database itself (the app does not create the schema/database — only its tables)
mysql -u root -p -e "CREATE DATABASE skoolyst_blog_management CHARACTER SET utf8mb4"

# 4. Run migrations (creates every blog_* table)
php bin/migrate.php

# 5. Seed the first admin account (admin@skoolyst.test / change-me-now — change this password immediately after logging in)
php database/seeders/seed_admin.php

# 6. Writable directories the app uploads/caches into
mkdir -p uploads/media uploads/images uploads/module-specific storage/cache/htmlpurifier storage/logs storage/temp
chmod -R 775 uploads storage      # Linux/macOS; skip on Windows/XAMPP

# 7. (Optional) create an API key for external/API access — prints the raw key once, save it
php bin/create-api-key.php "Some Client Name"
```

Then point the web server's document root at `public/` and confirm `.htaccess` rewriting is active (Apache: `AllowOverride All` on that vhost).

### Updating an existing deployment
```bash
git pull

composer install --no-dev --optimize-autoloader   # picks up any new/updated dependencies
php bin/migrate.php                                # applies any new migration files only — already-run ones are skipped
```
Nothing else needs to be re-run for a routine update — seeding is one-time (it no-ops if the admin account already exists) and there's no asset build step (CSS/JS under `public/assets/` are plain files, not compiled).

### Other commands
| Command | What it does |
|---|---|
| `php bin/migrate.php` | Run any pending migrations. |
| `php bin/migrate.php rollback` | Roll back the most recent migration batch. |
| `php database/seeders/seed_admin.php` | Create the default admin account (`admin@skoolyst.test`) — safe to re-run, no-ops if it already exists. |
| `php bin/create-api-key.php "Name"` | Generate a new Bearer API key for `routes/api.php`. |
| `composer install` | Install PHP dependencies (add `--no-dev` in production). |
| `vendor/bin/phpunit` | Run the test suite. |

### Notes
- There is no front-end build step — `resources/css` and `resources/js` are hand-copied into `public/assets/` (not compiled/bundled), so a deploy is just the files themselves; nothing to `npm run build`.
- `uploads/` and `storage/cache|logs|temp` are gitignored and must exist and be writable on the server — they aren't created by `composer install` or migrations.
- `storage/cache/htmlpurifier` specifically is HTMLPurifier's own definition cache (post body sanitization); if it's missing or unwritable, sanitization still works but silently loses the cache and re-parses its config on every request.
- Never commit `.env`; copy `.env.example` on each new environment and fill in real credentials there.





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
- [x] `index.php` → Home — hero/search, featured + latest post grids, newsletter form
- [x] `blog.php` → Archive — search, category filter, sort, pagination
- [x] `category.php` → Category archive — title/description driven by `?cat=` slug
- [x] `post.php` → Single post — cover, body, tags, author block, comments + comment form (**share buttons not built** — flagged non-blocking/cosmetic in Phase 7, still true)
- [ ] `about.php` → Static content **only** — the team grid was never actually built (`resources/views/frontend/about.php` is one static paragraph; `PageController::about()` passes no author/team data at all). Not called out as a deliberate cut in any prior phase report — a real gap surfaced while updating this checklist, not a previously-known trade-off.
- [x] `contact.php` → Contact form with real server-side handling (`PageController::submitContact`, validated, flashed)

#### Dashboard screens (auth-gated)
- [x] `dashboard/login.php` → Login — real session auth, no hard-coded credentials (Phase 4)
- [x] `dashboard/index.php` → Overview — stat cards, recent posts table (via `DashboardService`; no monthly-views chart was built — nothing in the schema/UI ever tracked that beyond `blog_post_views_daily`, which nothing renders yet)
- [x] `dashboard/posts.php` → Posts list/manage
- [x] `dashboard/post-editor.php` → Create/edit post form (title, slug, cover, category, tag picker, status)
- [x] `dashboard/categories.php` → Categories CRUD (modal-driven edit, per the original ZIP's UI)
- [x] `dashboard/media.php` → Media library (upload/serve/delete)

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
- [x] `api.js` talked directly to Supabase from the browser — fully replaced by our own PHP session-auth (web) + versioned `routes/api.php` (Bearer-token JSON API); no Supabase calls carried over
- [x] `MOCK_CATEGORIES` / `MOCK_AUTHORS` undefined-global references — gone; zero references anywhere in `app/` (confirmed by grep in Phase 10)
- [x] Contact form and comment form are demo-only — both have real Controllers/Services now (`PageController::submitContact`, `CommentController::store`, both validated server-side)
- [x] Color tokens remapped to the blueprint theme in Phase 3 (`app.css` — Navy/Blue/Cyan/Gold tokens)
- [x] CSRF protection added on every state-changing web route (Phase 4, enforced centrally in `Router::dispatch()`)
- [x] Login page no longer hard-codes demo credentials — real session auth against `blog_users` (Phase 4)

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

### Phase 4 Deliverable — Authentication & Security Report

**Status:** PASS

**Completed:**
- [x] `users` table added to `database/sql/module_database.sql` (id, name, email, password, role: admin/editor/author, last_login_at, timestamps) — column names kept compatible with other Skoolyst modules per `User.php`'s existing convention
- [x] `app/Models/User.php`: auth-scoped queries only (`findByEmail`, `findById`, `create`, `touchLastLogin`, `forSession`) via raw PDO — the generic find/all/save active-record layer is still Phase 5's job; deliberately not built early
- [x] `app/Services/AuthService.php`: `attempt()` (bcrypt `password_verify`, session regeneration on success, updates `last_login_at`) and `logout()` (`Session::destroy()`), plus a session-based lockout (5 failed attempts → 5 minute lockout) — no extra table needed
- [x] `app/Controllers/AuthController.php`: `showLogin`, `login` (server-side `Validator` rules + inline field errors), `logout`
- [x] `routes/web.php` wired to the real controller: `GET/POST /login` (Guest-only), `GET /logout` (Auth-only)
- [x] CSRF protection centralized in `Router::dispatch()`: every non-GET/HEAD route (except ones tagged `Api`, deferred to Phase 8) is checked against `csrf_verify()`; failure → 419 (JSON) or a flashed error + redirect back (web) — added `csrf_verify()` to `app/Helpers/csrf.php`
- [x] `AdminMiddleware`'s TODO filled in: requires `role === 'admin'` specifically (403/redirect otherwise), separate from `AuthMiddleware` which only requires being logged in
- [x] Secure session handling reused from Phase 2's `Session::start()` (httponly/secure/samesite cookie params from `.env`) + `Session::regenerate()` on every successful login (fixation protection)
- [x] Added `database/seeders/seed_admin.php` — one-off CLI script to create a default admin account for local testing (`php database/seeders/seed_admin.php`)
- [x] **Full end-to-end verification against a real MariaDB instance** (not just the sandbox autoload shim from Phases 2–3): seeded an admin, then verified — wrong password → rejected with an inline error; correct password → session created, `last_login_at` updated, redirected to `/dashboard`; `/dashboard` unauthenticated → redirect to `/login`; `/dashboard` authenticated → 200; a POST to `/login` missing the CSRF field → blocked (419 → redirect); 5 failed attempts in a row → 6th attempt blocked with the lockout message; `/logout` → session cleared, `/dashboard` redirects again. No PHP errors in any case.

**Files changed:**
- New: `app/Services/AuthService.php` (implemented), `app/Models/User.php` (implemented), `app/Controllers/AuthController.php` (implemented), `database/seeders/seed_admin.php`
- Modified: `database/sql/module_database.sql` (users table), `app/Helpers/csrf.php` (`csrf_verify()`), `app/Core/Router.php` (CSRF enforcement + 419 handler), `app/Middleware/AdminMiddleware.php` (role check), `routes/web.php` (real login/logout routes), `resources/views/auth/login.php` (inline validation errors)

**Assumptions:**
- No public registration/forgot-password screens were built — this module's dashboard is an internally-provisioned CMS (accounts created via `User::create()`/the seeder, not self-service signup). Flag if that's wrong and I'll add them.
- `GET /logout` (not `POST`) for simplicity, matching the blueprint's minimal style; it's CSRF-exempt by virtue of being a GET request. This is a common, low-risk trade-off (worst case is a forced logout, not account takeover) but can be hardened to POST-only later if you'd prefer.
- Lockout state lives in the session (not a database table) — resets if the person clears cookies. Fine for this scale; a persistent `login_attempts` table would be the Phase-later hardening if brute-force from rotating sessions becomes a real concern.
- Verified against a real MariaDB instance in this sandbox (not committed/persisted) rather than the Phase 2/3 autoload-only shim, since this phase's logic is meaningless without a real database round-trip.

**Remaining:**
- [ ] Phase 5 — Database & Models (generic active-record layer, blog-specific tables: posts, categories, tags, comments, media)

**Next Phase (Phase 5 — Database & Models):**
Design the blog schema (posts, categories, tags, post_tags, comments, media) informed by the Supabase migration reviewed in Phase 1, build out `Model`'s generic active-record methods, and create `Post`/`Category`/`Tag`/`Comment`/`Media` models on top of it.

**STOPPED — Waiting for your approval to continue.**

## Phase 5 — Database & Models

* Create module database structure.
* Add migrations/SQL.
* Create Models.
* Keep database logic out of Controllers and Views.

<!-- example tables  -->
blog_api_keys
blog_audit_log
blog_categories
blog_comments
blog_media
blog_migrations
blog_posts
blog_post_tags
blog_post_views_daily
blog_tags
blog_users
**CHECKPOINT → STOP AND REPORT**

### Phase 5 Deliverable — Database & Models Report

**Status:** PASS

**Completed:**
- [x] All 11 requested tables created as versioned migrations in `database/migrations/` (not a single dump file — see Assumptions): `blog_users`, `blog_categories`, `blog_posts`, `blog_tags`, `blog_post_tags`, `blog_comments`, `blog_media`, `blog_post_views_daily`, `blog_audit_log`, `blog_api_keys`, plus the migration-tracking table `blog_migrations` itself
- [x] Column shapes ported from the Supabase schema reviewed in Phase 1 (types adapted Postgres→MySQL: `uuid`→`INT UNSIGNED AUTO_INCREMENT`, `timestamptz`→`DATETIME`, `jsonb`→`JSON`); FKs added (`blog_posts.category_id/author_id`, `blog_post_tags`, `blog_comments.post_id`, `blog_media.uploaded_by`, `blog_post_views_daily.post_id`, `blog_audit_log.user_id`, `blog_api_keys.user_id`); RLS policies were Postgres/Supabase-specific and are not applicable here — that authorization now lives in the PHP middleware/role layer from Phase 4
- [x] `app/Core/Migrator.php` + `bin/migrate.php`: `php bin/migrate.php` runs pending migrations, `php bin/migrate.php rollback` undoes the last batch, tracked in `blog_migrations`
- [x] `app/Core/Model.php` implemented as a generic active-record base: `all`, `find`, `where`, `count`, `create`, `update`, `delete`, `paginate` — all fillable-filtered, all parameterized (no injection surface)
- [x] Models built on top of it: `Category`, `Tag` (incl. `attachToPost`/`detachFromPost`/`forPost` for the `blog_post_tags` pivot), `Post` (soft delete via `deleted_at`, `findBySlug`, `incrementViews`, `paginatePublished`/`paginateForDashboard`), `Comment` (defaults to `status=pending`), `Media`, `AuditLog` (`record()` helper for Phase 6+ to call), `ApiKey` (hash-only storage, for Phase 8)
- [x] **Phase 4 correction:** `users` → `blog_users`, per your README note — `app/Models/User.php` updated (kept as its own specialized static-method class rather than migrating it onto the generic `Model` base, to avoid touching `AuthController`/`AuthService`'s call sites; see Assumptions), `database/seeders/seed_admin.php` unchanged (works as-is)
- [x] Old `database/sql/module_database.sql` single-dump approach retired in favor of the migrations folder (left as a pointer comment, not deleted, so the file's history stays legible)
- [x] **Verified against a fresh, real MariaDB database** (dropped and recreated to prove the migrations build the whole schema from nothing): all 11 tables created; re-running `migrate` is a no-op; `rollback` cleanly drops everything in reverse order and `migrate` rebuilds it identically
- [x] **Full Model CRUD verified end-to-end**: created a user/category/post/tag/comment/media/audit row; `Tag::attachToPost` + `forPost` round-tripped correctly; `Post::update` then soft-`delete` correctly makes `find()`/`findBySlug()` return null while the row (and `deleted_at`) still exists in the table; `paginateForDashboard` correctly excluded the soft-deleted post from its count
- [x] **Regression-checked Phase 4's auth flow** against the renamed `blog_users` table end-to-end (seed → login → session → `/dashboard` 200) — no breakage from the rename

**Files changed:**
- New: `app/Core/Migrator.php`, `bin/migrate.php`, `database/migrations/*.php` (10 files), `app/Models/{Category,Tag,Post,Comment,Media,AuditLog,ApiKey}.php`
- Rewritten: `app/Core/Model.php` (generic active-record base)
- Modified: `app/Models/User.php` (table renamed to `blog_users`), `database/sql/module_database.sql` (deprecated in favor of migrations)

**Assumptions:**
- Built a proper migration system (`database/migrations/` + `blog_migrations` tracking table + `bin/migrate.php`) rather than one static SQL dump, since `blog_migrations` was explicitly in your table list — this also matches the empty `database/migrations/.gitkeep` placeholder already in the blueprint.
- `User` (Phase 4) stays a specialized static-method class rather than being refactored onto the new generic `Model` base — the content models (`Post`, `Category`, etc., all new this phase) use `Model` from the start. Happy to unify `User` onto `Model` too if you'd rather have one consistent pattern; it would mean small edits to `AuthController`/`AuthService`'s call sites.
- `blog_api_keys` and `blog_audit_log` have tables + basic models now, but nothing calls them yet — `AuditLog::record()` gets wired into Services in Phase 6, `ApiKey` gets wired into `ApiMiddleware` in Phase 8.
- Verified against a real (temporary, unpersisted) MariaDB instance in this sandbox, same as Phase 4 — not committed.

**Remaining:**
- [ ] Phase 6 — Services & Controllers (Post/Category/Comment/Media Services + Controllers, wiring the real dashboard/frontend routes on top of these Models)

**Next Phase (Phase 6 — Services & Controllers):**
Build `PostService`/`CategoryService`/`CommentService`/`MediaService` (business logic + `AuditLog` calls) and their Controllers, then replace the temporary closures in `routes/web.php` (home, dashboard) with real routes across `web.php`/`admin.php` for all 12 screens mapped back in Phase 1.

**STOPPED — Waiting for your approval to continue.**

## Phase 6 — Services & Controllers

* Create module Services.
* Create Controllers.
* Move business logic into Services.
* Connect Controllers → Services → Models.
* Keep Controllers thin.

**CHECKPOINT → STOP AND REPORT**

### Phase 6 Deliverable — Services & Controllers Report

**Status:** PASS

**Completed:**
- [x] Services: `PostService` (homepage/archive/search/pagination, slug generation with collision-safe suffixing, tag syncing, soft delete, audit logging), `CategoryService`, `CommentService` (submissions always saved as `pending`), `MediaService` (upload via a new `handle_upload()` helper, delete also removes the file from disk), `DashboardService` (stats + recent posts)
- [x] Controllers: `PostController` (public: `home`/`index`/`show`; admin: `adminIndex`/`create`/`store`/`edit`/`update`/`destroy`), `CategoryController` (public `show`; admin CRUD), `CommentController` (`store`), `MediaController` (admin `index`/`upload`/`destroy`, plus public `serve` — see below), `PageController` (`about`/`contact`/`submitContact`/`newsletter`), `DashboardController` rewritten to use `DashboardService`
- [x] All 12 screens mapped in Phase 1 are now wired to real routes/data: `routes/web.php` (public) and a new `routes/admin.php` (registered onto the same `Router` instance web.php requires, so there's still one dispatch call) replace every temporary closure from Phases 2–4
- [x] `resources/views/frontend/{blog,category,post,about,contact}.php` and `resources/views/admin/{posts/index,posts/edit,categories/index,media/index}.php` created; `home.php` and `admin/dashboard.php` (from Phase 3) rewired from placeholder to real data
- [x] Uploaded files are stored **outside** `public/` (in a project-root `uploads/` dir) and only reachable through `MediaController@serve` (`GET /media/{filename}`) — deliberate: keeps arbitrary uploaded files from being directly web-addressable/executable
- [x] `Model::rawQuery()`/`rawScalar()` added for the one case the generic helpers don't cover (search's `LIKE`, in `PostService::publicList`)
- [x] Two small correctness fixes surfaced by testing, unrelated to this phase's main scope but worth fixing now: `Router::invoke()` now casts numeric route params (e.g. `{id}`) to `int` (typed Controller params were failing under `strict_types` otherwise); `bootstrap/app.php` now `require_once`s `helpers.php` (was a plain `require`, which would fatal once real `composer install` runs, since `composer.json` also autoloads that same file)
- [x] **Full end-to-end verification against real MariaDB + a live PHP server**, not just lint: every public page (`/`, `/blog`, `/category/{slug}`, `/post/{slug}`, `/about`, `/contact`) returns 200; a public comment submission lands as `pending`; full admin login → create/edit/delete a post (including slug auto-generation and soft delete) → create/delete a category → upload a real PNG, serve it back via `/media/{filename}`, then delete it (file removed from disk too) — all verified with real HTTP requests and real DB state checks, not just code review

**Files changed:**
- New: `app/Services/{PostService,CategoryService,CommentService,MediaService,DashboardService}.php`, `app/Controllers/{PostController,CategoryController,CommentController,MediaController,PageController}.php`, `routes/admin.php`, 9 new/rewritten view files
- Modified: `app/Controllers/DashboardController.php`, `app/Core/Model.php` (`rawQuery`/`rawScalar`), `app/Core/Router.php` (int param casting), `app/Helpers/upload.php` (implemented), `app/Helpers/format.php` (`format_bytes`), `bootstrap/app.php`, `composer.json` (`ext-mbstring`, `ext-pdo`), `resources/views/{admin/dashboard,frontend/home}.php`, `routes/web.php`, `resources/css/app.css` (+ mirrored to `public/assets/css/app.css`)

**Assumptions:**
- No dedicated comment-moderation admin screen was built — it wasn't among the 12 screens mapped in Phase 1's ZIP inventory. `CommentService::approve()/reject()` exist and are ready to wire up if you want that screen added.
- Contact form and newsletter signup only flash an acknowledgment — no email/ticketing system or subscriber list exists to actually send/store anything yet. Say the word if you want a real integration (e.g. SMTP, or a `blog_subscribers` table).
- Category editing UI wasn't built (the ZIP's version was modal-driven); `CategoryController@update`/`CategoryService::update()` exist and are routed, just not triggered from the current add/delete-only categories screen — natural fit for Phase 7's UI/UX pass.
- Discovered `php-mbstring` and `php-gd`-style extensions are exercised by this code (mbstring by `Validator`'s `mb_strlen`, and real image validation by `MediaService`'s MIME check) — added `ext-mbstring`/`ext-pdo` to `composer.json`'s `require` as a result; most hosts have these by default, flagging in case yours doesn't.

**Remaining:**
- [ ] Phase 7 — Views & Functionality (richer post editor, tag picker UI, SEO meta tags in `<head>`, any remaining polish/edge cases across the 12 screens)

**Next Phase (Phase 7 — Views & Functionality):**
Polish pass across all screens: a tag picker on the post editor (backend already supports it via `PostService::syncTags`), rendering `seo_title`/`seo_description` into `<head>`, category edit UI, and any remaining fit-and-finish against the original ZIP's design.

**STOPPED — Waiting for your approval to continue.**

## Phase 7 — Views & Functionality

* Convert all frontend screens to PHP Views.
* Connect real data.
* Implement forms, CRUD, search, filters, pagination and required interactions.
* Complete admin and frontend functionality.

**CHECKPOINT → STOP AND REPORT**

### Phase 7 Deliverable — Views & Functionality Report

**Status:** PASS

Most of this phase's bullets (real data, forms, CRUD, search/filters/pagination) were already delivered in Phase 6, since Controllers needed real views to render against. This phase closes the gaps flagged as deferred back then:

**Completed:**
- [x] **Tag picker on the post editor**: checkboxes for every existing tag (pre-checked on edit) plus a free-text "add new tags, comma-separated" field. New `PostService::syncTagsFromEditor()` creates any tags that don't exist yet (matched by slug) and syncs the full attach/detach set — backed by `PostService::syncTags`, which already existed but had nothing calling it from a Controller until now
- [x] **Category edit UI**: added, using the modal component from Phase 3 (matching the original ZIP's modal-driven categories screen noted in Phase 1) — each row gets an "Edit" button that opens a pre-filled modal, `POST /dashboard/categories/{id}` (already routed in Phase 6, just not triggered from any UI until now)
- [x] **SEO meta tags**: `head.php` now renders `<meta name="description">`, `og:title`/`og:description`/`og:image`, and `<link rel="canonical">` when a view supplies them; wired into every frontend Controller (home, blog, category, post, about, contact) — the post page uses `seo_title`/`seo_description` when set, falling back to the title/excerpt
- [x] **Author byline** on the post page (`by {name}`), pulled via `User::findById()` from the post's `author_id` — was missing since Phase 1's mapping
- [x] `.tag-picker`/`.tag-picker-option` styles added to `app.css` (mirrored to `public/assets/css/app.css`)
- [x] Bug found and fixed during testing: `CategoryController@update` was sending `color => null` when the edit modal (which has no color field) submitted, violating `blog_categories.color`'s `NOT NULL` constraint (500 error) — now filters out empty/absent fields before updating, so only what's actually submitted changes
- [x] **Verified end-to-end against real MariaDB + a live server**: created a post with two brand-new tags via the free-text field, confirmed both tags were created and attached in `blog_post_tags`, confirmed the edit page shows them pre-checked, confirmed the public post page renders both tag badges, the author byline, and the `og:title`/canonical meta tags; category modal update tested (including the color-null bug and its fix); re-ran a full regression sweep of every public + admin page — all 200, no PHP errors or warnings

**Files changed:**
- Modified: `app/Services/PostService.php` (`syncTagsFromEditor`), `app/Controllers/{PostController,CategoryController,PageController}.php`, `resources/views/admin/posts/edit.php` (tag picker), `resources/views/admin/categories/index.php` (edit modals), `resources/views/components/head.php` (SEO/OG tags), `resources/views/frontend/post.php` (author byline), `resources/css/app.css` (+ mirrored copy)

**Assumptions:**
- Didn't add share buttons to the post page (mentioned in Phase 1's screen inventory but cosmetic/non-blocking) or a dedicated tag-management screen (create/rename/delete tags outside of the post editor) — flag if either matters to you.
- Contact/newsletter forms and comment moderation remain as scoped in Phase 6 (acknowledgment-only, no admin moderation screen) — unchanged this phase.

**Remaining:**
- [ ] Phase 8 — API (JSON endpoints, `blog_api_keys` auth via `ApiMiddleware`)

**Next Phase (Phase 8 — API):**
Build versioned JSON endpoints in `routes/api.php` (likely read-only: list/show posts, categories) authenticated via `blog_api_keys` + `ApiMiddleware`, which has had a `TODO` since Phase 2.

**STOPPED — Waiting for your approval to continue.**

## Phase 8 — API

* Implement API routes under `routes/api.php`.
* Use consistent JSON responses.
* Add API middleware/authentication where required.
* Validate all API input.

**CHECKPOINT → STOP AND REPORT**

### Phase 8 Deliverable — API Report

**Status:** PASS

**Completed:**
- [x] Versioned JSON API under `routes/api.php` (registered onto the shared `Router`, same pattern as `routes/admin.php`): `GET /api/v1/posts`, `GET /api/v1/posts/{slug}`, `POST /api/v1/posts/{slug}/comments`, `GET /api/v1/categories`, `GET /api/v1/categories/{slug}`
- [x] `ApiMiddleware`'s Phase-2 TODO filled in: Bearer-token auth against `blog_api_keys` (SHA-256 hash comparison, `revoked_at IS NULL`), touches `last_used_at` on every authenticated call, 401 JSON on a missing/invalid/revoked key
- [x] `bin/create-api-key.php "Client Name"` — CLI to issue a key (no admin UI screen for this, matching the same reasoning as comment moderation in Phase 6: not one of the 12 mapped screens); the raw key is shown once, only its hash is ever stored
- [x] Consistent JSON envelope: `{"data": ..., "meta": {...}}` for lists/detail, `{"error": ...}` (plus `"errors"` for field-level validation messages) for failures; `PostService::toApiArray()` added so the shape is identical whether a post comes from `/posts`, `/posts/{slug}`, or nested under `/categories/{slug}`
- [x] All API input validated the same way as the web forms (`Core\Validator`) — a bad comment submission gets `422` with per-field messages, not a generic error
- [x] CSRF is correctly skipped for `Api`-tagged routes (already handled by the Phase 4 Router logic) — Bearer-token auth is the right mechanism here, not a CSRF token
- [x] Comment endpoint reuses `CommentService::submit()` from Phase 6 — API-submitted comments are `pending` too, same moderation rule as the web form
- [x] **Verified end-to-end against real MariaDB + a live server**: created a key via the CLI, confirmed a missing/wrong Bearer token both return 401, a valid key lists/shows posts and categories correctly (including nested posts inside a category using the identical shape as the top-level list), submitted a comment via the API (valid → 201 pending, invalid → 422 with field errors), and confirmed `last_used_at` updates on each authenticated call

**Files changed:**
- New: `app/Controllers/Api/{PostController,CategoryController,CommentController}.php`, `routes/api.php`, `bin/create-api-key.php`
- Modified: `app/Middleware/ApiMiddleware.php` (real auth), `app/Models/ApiKey.php` (`created_at` added to `$fillable`), `app/Services/PostService.php` (`toApiArray()`), `routes/web.php` (requires `api.php`)

**Assumptions:**
- No rate limiting was implemented — `blog_api_keys` has no request-count/window column to base it on, and it wasn't called out as required beyond "middleware/authentication where required." Flag it if you want real throttling; it'd need a small schema addition (a migration) to track it properly.
- No admin UI for managing API keys (issue/list/revoke) — the CLI covers issuing; `ApiKey::revoke()` already exists in the model from Phase 5 if you want a screen for it later.
- Kept the API read-only for posts/categories plus one write action (comments), matching what a public blog typically exposes; nothing here lets an API caller create/edit/delete posts — that still requires a logged-in session via the web dashboard.

**Remaining:**
- [ ] Phase 9 — Responsive & UI QA
- [ ] Phase 10 — Final QA
- [ ] Phase 11 — Documentation

**Next Phase (Phase 9 — Responsive & UI QA):**
Pass over every screen at mobile/tablet/desktop widths, checking the responsive rules from Phase 3 actually hold up now that real (and sometimes longer/messier) content is flowing through them.

**STOPPED — Waiting for your approval to continue.**

## Phase 9 — Responsive & UI QA

* Test every screen on mobile, tablet and desktop.
* Fix layout overflow, spacing, typography, navigation and component issues.
* Ensure header/footer/sidebar work correctly everywhere.

**CHECKPOINT → STOP AND REPORT**

### Phase 9 Deliverable — Responsive & UI QA Report

**Status:** PASS

No real browser/screenshot tool is available in this environment (tried installing Puppeteer + Chromium; the browser binary download is blocked by the sandbox's network allowlist), so this pass is a structural CSS/HTML audit against the mobile/tablet/desktop breakpoints already established in Phase 3, stress-tested with deliberately awkward content (a 130-character post title, a long category name, and post body HTML containing a raw `<img>` and `<table>` — the kind of thing a real author might paste in) rather than a pixel-level visual check. Flagging this limitation directly rather than claiming a browser-verified pass.

**Completed:**
- [x] `.table-wrap { overflow-x:auto }` and `.table td { word-break:break-word }` are now unconditional (previously only applied under a `max-width:768px` media query) — a wide admin table can overflow just as easily on a mid-size tablet as on a phone
- [x] Global `img { max-width:100%; height:auto }` added — guards against any image (post covers, and now arbitrary `<img>` tags pasted into a post body) overflowing its container regardless of the image's own dimensions
- [x] `.post-body img/table/pre` given explicit overflow handling (`display:block; overflow-x:auto` for tables, `max-width:100%` for images/pre blocks) — user-authored post HTML can contain wide tables or code blocks that would otherwise blow out the layout
- [x] `.hero-search` and `.newsletter form` given `flex-wrap:wrap` — previously unwrapped flex rows, fine down to ~480px but not defensively wrapped below that
- [x] `.hero h1` sized down on mobile (2rem → 1.5rem) and `.admin-main` padding tightened (1.5rem → 1rem) under 768px
- [x] Verified header/footer/sidebar: navbar hamburger collapse (<768px) and admin sidebar toggle (<900px) still fire correctly with real (not placeholder) nav data; footer's flex-wrap already handled long content fine, no change needed
- [x] Re-ran the full page sweep (public + admin) with the deliberately-long test content above — every page still 200s, `post-body` correctly wraps the injected `<img>`/`<table>`, no layout-breaking markup found

**Files changed:**
- Modified: `resources/css/app.css` (+ mirrored to `public/assets/css/app.css`)

**Assumptions:**
- No headless-browser visual regression testing was possible here (see above) — if pixel-perfect verification across real devices matters before launch, that's worth doing manually or with BrowserStack/similar once this is deployed somewhere reachable.
- Didn't touch `admin.css`'s sidebar/topbar rules — Phase 3's breakpoints there already held up fine against the longer test content.

**Remaining:**
- [ ] Phase 10 — Final QA
- [ ] Phase 11 — Documentation

**Next Phase (Phase 10 — Final QA):**
A holistic pass over the whole module — re-check every Controller/Service/Model against the blueprint's own coding conventions, look for anything Phases 1–9 might have missed or left inconsistent, and a final full regression sweep before Phase 11 writes it all up.

**STOPPED — Waiting for your approval to continue.**

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

### Phase 10 Deliverable — Final QA Report

**Status:** PASS WITH WARNINGS

A holistic regression + security pass over the whole module, tested against a **real, dedicated local MariaDB database** (`skoolyst_blog_management`, freshly migrated from scratch) and a live `php -S` server driven with real HTTP requests (curl, cookie-jar sessions, multipart uploads) — not lint-only.

**Environment fix (blocking, found first):** `.env`'s `DB_DATABASE` pointed at `skoolyst_teachers`, a large *shared* database belonging to unrelated Skoolyst modules (teachers/ads/etc. tables mixed in with a stray copy of this module's `blog_*` tables) — a direct violation of the blueprint's "each module owns its own database" rule. A second candidate, `skoolyst_blog`, turned out to belong to a genuinely separate sibling project (`Projects/skoolyst-blogs`) with its own older `.sql`-based migration history (`blog_migrations.batch` column didn't even exist — confirmed this was never this project's own schema). Neither was safe to reuse or drop. Created a new, dedicated `skoolyst_blog_management` database and repointed `.env` at it. **This is a local-only fix** (`.env` is gitignored) — flagging it clearly since it changes what a fresh `composer install && php bin/migrate.php` connects to on this machine.

**⚠️ Critical bug found post-report, by the user testing the real site in a browser (bug #7 below), that this phase's own automated testing structurally could not have caught:** every text/email/password/select field rendered through the shared `input` component across the *entire site* — login, contact, post editor, category forms, everything — had `name="input"` in the actual HTML instead of its real field name (`email`, `password`, `title`, …), because `component()`'s own parameter happened to also be called `$name` and silently won the collision. All of this phase's (and Phases 2–9's) "verified end-to-end" HTTP testing used curl/PHP scripts posting hand-specified, correct field names directly — never a real browser submitting the real rendered HTML — so this was invisible to every automated check run so far. Now fixed at the root; see bug #7 for the full writeup and the re-verification this triggered against real rendered forms specifically.

**Completed:**
- [x] **Migrations**: fresh `php bin/migrate.php` against the new empty database created all 11 tables correctly; re-running is a no-op; `php bin/migrate.php rollback` cleanly drops everything in one reversed batch and `migrate` rebuilds it identically — full round-trip verified
- [x] **Auth/authorization**: unauthenticated `/dashboard` → 302 to `/login`; wrong password → inline error, no session; 5 failed logins → 6th blocked with the lockout message (still blocked on the *correct* password too, confirming it's a real lockout not just a bad-password message); correct login → session + redirect; `/logout` → session cleared, `/dashboard` protected again
- [x] **CSRF**: every state-changing web route tested with the token stripped — all correctly rejected (redirect, no DB write), confirmed by row counts before/after
- [x] **Full CRUD verified with real DB state checks** (not just HTTP status codes): category create/edit (color preserved through the color-less edit modal, per Phase 7's fix — still holds), post create with new tags (tags created + attached in `blog_post_tags`), post edit (tag pre-check confirmed), soft-delete (row survives with `deleted_at` set, disappears from `find`/`findBySlug`), media upload → served → deleted (file removed from disk, `blog_media` row removed), comment submission (public form, always lands as `pending`), audit log correctly recording every action
- [x] **API**: missing/wrong Bearer token → 401; valid key lists/shows posts/categories in the documented envelope; comment endpoint validates (422) and creates (201) correctly; `last_used_at` updates
- [x] **Validation/security spot checks**: empty required fields correctly rejected (no row created); HTML/script-bearing post titles render fully escaped everywhere (title, meta tags, OG tags, listings) — no stored XSS in any escaped field; awkward content (long titles, `<img>`/`<table>` in post body) still renders inside the Phase 9 overflow guards
- [x] **Dead-code / structure audit** (delegated to a read-only sub-agent, findings independently verified): Controllers confirmed thin everywhere; all raw SQL is parameterized (no interpolated user input); all 29 views are `.php`; every user-controllable field in the views is escaped via `clean()` except `post.body`, which is intentionally raw (trusted author HTML, same trust model as Phase 4's internally-provisioned accounts)

**Bugs found and fixed this phase:**
1. **Soft-deleted posts leaked into public listings/API** — `PostService::forHomepage()` and the non-search branch of `publicList()` used the generic `Model::where()`/`Model::paginate()`, which (unlike `Post::find()`/`findBySlug()`/`count()`/`delete()`, all already overridden for this) never excluded `deleted_at`. A deleted post kept appearing on `/`, `/blog`, and `GET /api/v1/posts` even though its own page correctly 404'd. Fixed by overriding `Post::where()` and `Post::paginate()` to always exclude soft-deleted rows, matching the pattern the rest of the model already follows. Verified: the deleted post disappeared from all three surfaces, `api.meta.total` dropped correctly, and the still-live post was unaffected.
2. **Uncaught exceptions leaked raw stack traces (including full server file paths) to the browser as a `200 OK`** — reproduced via a tampered `category_id` on post create, which threw an uncaught `PDOException` (FK constraint violation) straight past `public/index.php` with no handler; the built-in PHP server rendered it as plain HTML source. Fixed by wrapping the dispatch call in `public/index.php` in a try/catch that logs the full exception to `storage/logs/app.log` (gitignored) and renders the already-existing `errors/500` view (or a JSON `{"error":"Server error"}` for API/AJAX requests) with a real `500` status — no more information disclosure, and it now degrades the same way the 404 path already did.
3. **A stale/tampered `category_id` (e.g. the category was deleted while a post's edit tab was still open) hit that same exception path** — added a small guard (`PostController::validCategoryId()`) that silently drops an invalid `category_id` to `null` instead of ever reaching the DB, so it's now a clean save rather than a crash even with the safety net from #2 in place.
4. **Double-escaped category name in the edit-category modal title** — `resources/views/admin/categories/index.php` pre-escaped the name with `clean()` before handing it to the `modal` component, which escapes `$title` itself; a category named e.g. "Tips & Tricks" rendered as "Tips &amp;amp; Tricks". Fixed by passing the raw name through (the component owns escaping, per its own docblock contract).
5. **Removed dead code**: `Post::paginatePublished()` was already unused before this phase and became fully redundant once fix #1 made the base `paginate()` exclude soft-deletes too — deleted it rather than leave two near-identical methods around.
6. **The app was completely unroutable when deployed under a subdirectory (its actual XAMPP deployment mode) — found live by the user testing `/blog` in a browser after this report was first written.** `Request::uri()` returned the raw `REQUEST_URI` (e.g. `/Projects/Skoolyst-blgo-management-system/Skoolyst-blgo-management-system/public/blog`) with no stripping of the app's own mount-point prefix, so the Router's `#^/blog$#`-style patterns never matched anything — every real page 404'd, including the homepage. **This slipped through the rest of Phase 10 because that testing ran against a root-mounted `php -S 127.0.0.1:8091` dev server**, where there's no prefix to strip, so the bug was invisible there. Fixed by having `Request::uri()` strip `APP_URL`'s own path component (the same source of truth `url()` already uses to build every link in the app) from the incoming request path before the Router sees it. Re-verified the full public + admin + API route sweep directly against the real Apache URL (`http://localhost/Projects/Skoolyst-blgo-management-system/Skoolyst-blgo-management-system/public/`) — all correct now, including generated links/form actions and a full login → dashboard → CRUD flow.
7. **Every form field rendered `name="input"` instead of its real name, silently breaking every form on the site when submitted from a real browser — found live by the user, who saw the login form claim both "email is required" and "password is required" after they'd filled in both, with the email box then showing the seeded account's *password* value ("change-me-now") courtesy of confused browser autofill.** Root cause: `app/Helpers/component.php`'s `component(string $name, array $data)` used `$name` as its own parameter name for the component's filename (e.g. `"input"`), then called `extract($data, EXTR_SKIP)` to bind `$data`'s keys as local variables for the required view. `EXTR_SKIP` refuses to overwrite a variable that already exists — and `$name` already existed (bound to `"input"`, the component's own filename) in every single call like `component('input', ['name' => 'email', ...])`. So the `input.php`/`select.php` template's own `$name` (the *form field's* name) was silently never set to `"email"` — it stayed `"input"`, the outer function's parameter. This affected **every field on every form in the app** (login, contact, post editor, category forms, etc.) rendered via `component('input', …)` or `component('select', …)` — all of them shared the literal `name="input"`/`id="field-input"`, meaning a real browser's form serialization only ever sent one value under the key `"input"` (last field in the DOM wins), and the server saw `email`/`password`/`title`/etc. as always-missing. This is exactly why the login page showed both fields as "required" even when filled in, and the duplicate `id` also broke `<label for>` association and confused the browser's autofill/credential matching (explaining the password value appearing in the email box). **Why none of Phases 2–9's or this phase's own automated testing caught it:** every prior verification pass posted hand-written, correctly-named fields directly via curl/PHP scripts (`--data-urlencode "email=..."` etc.) rather than parsing and resubmitting the actual rendered HTML the way a browser does — so the bug was invisible to every check that ran before a human actually used the real page. Fixed by renaming `component()`'s own parameter to `$__componentFile` so it can no longer collide with a component's own `name`/`id`/etc. data keys (`app/Helpers/component.php`). While in there, also added `autocomplete="username"`/`"current-password"` to the login form's fields (defense in depth against browser autofill misassignment, `resources/views/auth/login.php`, `resources/views/components/input.php`) and stopped echoing the submitted password back into the password field on a validation-error re-render. **Re-verified by parsing real rendered HTML and resubmitting using its own field names** (not hand-picked names) across login, contact, category-create, and confirmed every form site-wide now renders its correct `name`/`id` (`title`, `slug`, `category_id`, `status`, `seo_title`, `message`, `description`, `file`, etc.) — all correct now.

**Reviewed, left as-is (intentional, not oversights):**
- `AdminMiddleware` (role === 'admin' check, built Phase 4) is implemented but not attached to any route — every `/dashboard/*` route only requires `['Auth']`, so any authenticated user (regardless of `admin`/`editor`/`author` role) can reach every admin screen. Its own docblock scopes it to "e.g. user/settings management," and no such screen exists yet (nor does anything provision non-admin accounts — only `seed_admin.php`, which is admin-only). Documenting this as a known, currently-inert extension point rather than wiring it to routes on my own judgment, since that would be a real authorization-behavior change beyond "final QA."
- `Comment::pending()` / `CommentService::pending()`/`approve()`/`reject()` and `ApiKey::revoke()` are unused — both were explicitly built ahead of screens Phase 6/8 deferred on purpose (comment moderation, API key management UI), not accidental leftovers.
- Duplicated `slugify()` logic between `PostService`/`CategoryService`, and repeated constructor/audit-log boilerplate across the four content Services — real but cosmetic duplication; left alone rather than refactoring mid-QA without being asked, per the "don't refactor beyond what's needed" rule.
- `MediaController::upload()` reads `$_FILES['file']` directly instead of through `Request` (which has no file-upload accessor) — a structural convention gap, not a bug; upload/delete were both verified working correctly.

**Files changed:**
- `app/Models/Post.php` (soft-delete-safe `where()`/`paginate()` overrides; removed dead `paginatePublished()`)
- `app/Controllers/PostController.php` (`validCategoryId()` guard on create/update)
- `public/index.php` (global exception handler → logged + themed 500 / JSON error)
- `resources/views/admin/categories/index.php` (fixed double-escaped modal title)
- `app/Core/Request.php` (`uri()` now strips the app's subdirectory mount point before route matching)
- `app/Helpers/component.php` (renamed the function's own parameter so it can't collide with a component's `name` data key — the site-wide form-field-naming bug)
- `resources/views/components/input.php` (added `autocomplete` support)
- `resources/views/auth/login.php` (autocomplete hints; password field no longer echoes the submitted value back)
- `.env` (local-only, gitignored: `DB_DATABASE` repointed from the shared/wrong database to a dedicated `skoolyst_blog_management`)

**Tests / Checks performed:**
- Fresh migrate → rollback → re-migrate round-trip against real MariaDB
- Full HTTP-level regression via curl with real cookie-jar sessions: every public route, every admin route (authenticated and not), every API route (keyed and not), covering all CRUD verbs across posts/categories/media/comments
- Every mutation cross-checked against actual MySQL row state (not just HTTP status codes) — including the two bugs above, which only showed up by checking the database/response body rather than trusting a `200`/`302`
- `php -l` on every changed file; full server log reviewed for warnings/notices/deprecations across the entire session — none found, before or after fixes
- Read-only sub-agent audit of Controllers-thin-ness, dead code, duplicate logic, raw-SQL injection surface, and view-layer output escaping across the whole `app/` and `resources/views/` trees
- **Re-verified the entire route sweep a second time directly against the real Apache/XAMPP deployment** (`http://localhost/Projects/Skoolyst-blgo-management-system/Skoolyst-blgo-management-system/public/`) after bug #6 surfaced — not just the `php -S` dev server used for the rest of this phase
- **Re-verified every form site-wide by parsing real rendered HTML and resubmitting with its own field names** (not hand-picked ones) after bug #7 surfaced — login, contact, category create, confirmed correct `name`/`id` attributes across the post editor, media upload, and every remaining admin form

**Warnings / Issues:**
- Status is PASS WITH WARNINGS rather than a clean PASS because of the two "reviewed, left as-is" items with real (if currently low-impact) implications: the inert `AdminMiddleware` means there's no actual admin/editor/author privilege separation despite the schema supporting it, and there's genuinely no way yet to create a non-admin account to even test that separation.
- No headless-browser visual verification (same sandbox limitation noted in Phase 9) — this phase is HTTP/DB-level correctness, not pixel QA.
- **Methodology gap that let bugs #6 and #7 through this phase's first pass, worth carrying into how future phases get tested:** every "verified end-to-end" claim in this report (and in Phases 2–9's) was built on curl/PHP scripts posting hand-specified, correctly-named fields and hitting a root-mounted dev server — never an actual browser parsing and resubmitting the real rendered HTML through its real deployment path. Both bugs were structurally invisible to that style of test and only surfaced once the user exercised the real page in a real browser. The fixes are verified the same rigorous way (real DB/response checks), but this class of "the HTML itself is wrong even though the backend logic is right" bug is worth a real-browser click-through pass (not just curl) before this module is considered launch-ready, since this session had no browser-automation tool available (see Phase 9's tooling note).

**Assumptions:**
- Treated "remove duplicate/dead code" as removing genuinely accidental dead code, not intentionally-deferred forward-looking API surface that prior phase reports already explained — see the "reviewed, left as-is" list above for the reasoning on each.
- Did not wire `AdminMiddleware` into any route myself, since that changes real authorization behavior (who can do what) rather than fixing a defect — flag if you'd like it attached to specific admin-only routes once there's a way to create non-admin accounts.

**Remaining:**
- [ ] Phase 11 — Documentation

**Next Phase (Phase 11 — Documentation):**
Write up setup/install steps (including the corrected `.env` database-per-module guidance this phase surfaced), database configuration, routes/API reference, and a tour of the module's Controllers/Services/Models — then mark every checklist item in this README `[x]`.

**STOPPED — Waiting for your approval to continue.**

### Addition (between Phase 10 and 11) — Public Signup & Reader Role

Requested directly: `blog_users` only supported internally-provisioned admin/editor/author accounts (via seeder/CLI), with no public registration and no reader role. Added public signup, a `reader` role, and closed a real authorization gap this surfaced (the author/editor "manage own vs. all posts" split Phase 10 flagged as unbuilt).

**Completed:**
- [x] **`reader` role**: new migration `2026_09_04_000001_add_reader_role_to_blog_users.php` (`ALTER TABLE ... MODIFY COLUMN role ENUM('admin','editor','author','reader')`) — run against the real local `skoolyst_blog_management` database, confirmed via `SHOW COLUMNS`
- [x] **Public signup** (`GET`/`POST /signup`, Guest-only): `AuthService::register()` creates `author` or `reader` accounts only — admin/editor stay internally-provisioned, matching the dashboard's existing design intent. Rejects a duplicate email with a clean inline error (checked proactively via `User::findByEmail`, not just relying on the DB's unique constraint) and validates a `password_confirmation` match. Logs the new account in immediately (session regenerated, same fixation-safe pattern as login) and redirects readers to `/` and authors straight to `/dashboard`.
- [x] **`StaffMiddleware`**: new middleware (`admin`/`editor`/`author` only) replacing `Auth` on every `/dashboard/*` route in `routes/admin.php` — a reader hitting any dashboard URL (GET or POST, including a raw POST that bypasses the UI entirely) is redirected to `/` with a flash message, never reaching a Controller. `/logout` deliberately stays on plain `Auth` — readers can still log out.
- [x] **Author-vs-editor permission split** (previously confirmed **not** to exist during Phase 10's audit): `PostService::canManage()` — an `author` may only edit/update/delete their **own** posts; `editor`/`admin` manage all, unchanged. `PostController::adminIndex()` now scopes the posts list itself to `auth_user()['id']` when the viewer is an author (via `Post::paginateForDashboard($page, $perPage, $authorId)`), so an author's dashboard only ever lists their own work rather than showing everything with silently-broken edit links.
- [x] **Navbar**: guests get Login + Sign up; staff (admin/editor/author) get the Dashboard button as before; readers get their name + a Logout link instead of a Dashboard button they can't use.
- [x] `resources/views/auth/signup.php` (name, email, password, confirmation, an account-type select limited to Reader/Author) + a "Sign up"/"Sign in" cross-link between it and the login page.

**Files changed:**
- New: `database/migrations/2026_09_04_000001_add_reader_role_to_blog_users.php`, `app/Middleware/StaffMiddleware.php`, `resources/views/auth/signup.php`
- Modified: `app/Services/AuthService.php` (`register()`), `app/Controllers/AuthController.php` (`showSignup`/`signup`), `routes/web.php` (signup routes), `routes/admin.php` (`Auth` → `Staff` on all dashboard routes), `app/Models/Post.php` (`paginateForDashboard` gained an optional `$authorId` scope), `app/Services/PostService.php` (`canManage()`, `dashboardList()` scoping), `app/Controllers/PostController.php` (ownership checks on `adminIndex`/`edit`/`update`/`destroy`), `resources/views/components/navbar.php`, `resources/views/auth/login.php`, `resources/css/app.css` (+ mirrored `public/assets/css/app.css`)

**Tests performed (real local MySQL + live server, not lint-only):**
- Migration applied to the real DB; `SHOW COLUMNS` confirmed the 4-value enum
- Signed up as a **reader**: landed on `/`, not `/dashboard`; `GET /dashboard` → 302 to `/` with "The dashboard is for staff accounts only."; a raw `POST /dashboard/categories` (bypassing the UI entirely) also 302'd and created nothing (checked the DB directly); navbar showed Logout, not Dashboard; reader could still comment on a post (lands `pending`, same as anonymous)
- Signed up as an **author**: landed straight on `/dashboard`; created a post (owned by their own `author_id`); their own `/dashboard/posts` list showed only that post; attempting `/dashboard/posts/{id}/edit` on a different, pre-existing post 302'd with "You can only manage your own posts." and did not render the edit form
- Logged in as **admin** and as a separately-provisioned **editor** (created via `User::create()`, matching the "internally-provisioned" design — no self-service path to those roles, correctly): both saw the author's post in their full list and could open its edit page (200, not redirected) — confirming the split is author-only, not blanket-restrictive
- Duplicate-email signup correctly rejected inline ("That email is already registered.") without hitting the DB's unique-constraint error path; mismatched `password`/`password_confirmation` correctly rejected ("Passwords do not match.")
- Full server log reviewed across every request in this session — no PHP warnings/notices/errors

**Assumptions:**
- Signup only offers `author`/`reader` as account types — `admin`/`editor` remain seeder/CLI-provisioned, matching Phase 4's original "internally-provisioned CMS" design note. Flag if you actually want self-service editor signup too.
- No email verification step — a signup logs the account in immediately, same trust level as the rest of this module's auth (no mail sending exists anywhere in this codebase yet).
- Reader-specific public-site features beyond commenting (e.g. saved posts, a profile page) weren't requested and weren't built — "comment, etc." in the request was read as "whatever public-site actions already exist," not a request for new reader-only features.

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
