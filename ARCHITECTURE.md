# Skoolyst Module Architecture

## Request flow
Browser/API -> public/index.php -> Router -> Middleware -> Controller -> Service -> Model -> Database
                                                   |
                                                   -> View -> Components -> CSS/JS

## Layer responsibilities
- Core: framework-like infrastructure only.
- Controllers: HTTP orchestration; no heavy business logic.
- Services: business rules and integrations.
- Models: persistence/domain data.
- Helpers: small stateless/reusable functions.
- Middleware: cross-cutting request guards.
- Views: PHP templates only.
- Components: reusable UI fragments.
- Config: environment-backed configuration.
- Routes: web/admin/API route definitions.
- Public: only web-accessible entry point/assets.
- Storage: non-public logs/cache/temp.
- Uploads: module-owned uploaded files; configure server permissions carefully.
