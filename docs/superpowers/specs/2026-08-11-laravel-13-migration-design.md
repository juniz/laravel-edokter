# Laravel 13 Migration Design

Date: 2026-08-11  
Branch: `codex/migrate-laravel-13`  
Source baseline: Laravel 9.52.16, PHP 8.4.8

## Goal

Upgrade the existing eDokter application from Laravel 9 to Laravel 13 while preserving its current clinical workflows and public contracts. The migration must leave `master` untouched and produce an application that installs and runs on PHP 8.3+ with Laravel 13.

## Context and constraints

The application is a mature Laravel 9 system with:

- Livewire 2 components and Livewire Tables;
- custom request encryption/decryption, sanitization, authentication, and CSRF middleware;
- web and API routes, Sanctum, custom auth controllers, scheduled commands, BotMan, BPJS integrations, PDF generation, AdminLTE, PWA, and Vite assets;
- legacy Laravel bootstrap, HTTP kernel, console kernel, and service-provider structure;
- a test suite covering sanitization, surgical-area marking, Livewire behavior, and basic feature booting.

The current baseline has a valid `composer.json`, but the existing test command reports 10 failures and 5 passes. The failures include an HTTP 500 from the example route and tests that detect a non-testing environment; these are recorded as pre-migration baseline conditions and must be rechecked after the test environment is made deterministic.

Laravel 13 documentation consulted through Context7 establishes PHP 8.3 as the minimum and identifies relevant breaking changes, including the CSRF middleware rename to `PreventRequestForgery` and changes to default cache, Redis, and session key prefixes. The Laravel 13 upgrade guide is the authoritative reference for framework changes.

## Chosen approach

Use a staged in-place upgrade on `codex/migrate-laravel-13`:

1. Upgrade Laravel 9 to 10, then 11, 12, and 13.
2. Resolve direct and transitive dependencies at each major-version checkpoint.
3. Run the application boot checks and relevant tests at every checkpoint.
4. Keep the legacy application structure unless a concrete incompatibility requires a targeted structural change.
5. Commit each meaningful checkpoint separately so the migration can be reviewed or reverted by major version.

This is preferred over regenerating a Laravel 13 skeleton because the application has extensive custom middleware, providers, route behavior, Livewire components, and clinical integrations. A framework-only version bump is rejected because the current package set includes likely compatibility boundaries, especially Livewire 2, Sanctum, UI/auth helpers, DataTables, and Livewire Tables.

## Dependency strategy

- Raise the PHP constraint to `^8.3` at the final Laravel 13 checkpoint; use the minimum PHP constraint required by each intermediate framework version while upgrading.
- Update `laravel/framework` and its framework-coupled development packages together, including Sanctum, Tinker, Collision, Ignition, PHPUnit, and Faker where required.
- Audit and upgrade Livewire and Livewire Tables as a compatibility unit. Preserve component namespaces and behavior; change lifecycle hooks, events, validation, uploads, pagination, and test helpers only where the selected supported major requires it.
- Audit Yajra DataTables, Laravel UI, AdminLTE, PWA, BotMan, PDF wrappers, image packages, Gemini/Groq clients, and other direct dependencies. Prefer a supported upgrade; otherwise pin a compatible release or isolate the affected integration behind its existing boundary.
- Never edit `composer.lock` manually. All lockfile changes must come from Composer resolution.
- Do not change application secrets or production `.env` values as part of the framework migration.

## Application changes

### Bootstrap, middleware, and providers

- Preserve `bootstrap/app.php`, `App\Http\Kernel`, `App\Console\Kernel`, and existing providers while they remain supported.
- Update the custom CSRF middleware and all references in the HTTP kernel and Sanctum configuration to the Laravel 13-compatible implementation, using the new `PreventRequestForgery` base/API where required.
- Preserve the ordering and semantics of `SanitizeInputMiddleware`, request encryption/decryption, cookies, sessions, authentication, throttling, and route binding.
- Audit custom middleware aliases and any framework property or method renamed across Laravel 10–13.
- Keep schedule definitions, command registration, event registration, route loading, and authorization behavior intact unless a framework API requires a targeted adjustment.

### Configuration and runtime behavior

- Merge framework configuration changes selectively rather than replacing all configuration files with a fresh skeleton.
- Review cache, Redis, and session prefixes for the Laravel 13 hyphenated defaults. Preserve explicit application values where changing keys would create an operational compatibility problem; document any intentional prefix change.
- Rebuild config, route, view, and event caches during verification.
- Preserve existing web/API URLs, route names where practical, auth flows, Livewire endpoints, file uploads, PDF endpoints, BotMan routes, and BPJS interactions.

### Scope exclusions

No unrelated refactors, UI redesigns, database schema changes, data deletion, production migration execution, or domain behavior changes are included. A migration file is added only if a concrete framework compatibility issue proves it necessary.

## Verification design

At each framework checkpoint and at the final Laravel 13 state, run:

- `composer validate --strict` and a clean Composer install/update appropriate to the checkpoint;
- Composer autoload generation and package discovery;
- `php artisan about`, `php artisan route:list`, and representative boot commands;
- PHPUnit/Livewire tests in a deterministic `testing` environment, including middleware sanitization, surgical-area marking, auth, route/API, upload, and PDF regression coverage;
- `php -l` on changed PHP files and searches for obsolete framework namespaces/API calls;
- Vite asset build.

The testing environment must not depend on a developer `.env` or stale cached configuration. Existing baseline failures will be separated into environment failures and migration regressions before new failures are assessed.

## Completion criteria

The migration is complete when:

1. `composer install` resolves successfully on PHP 8.3+ with Laravel 13 and all active direct packages.
2. The application boots and discovers providers, routes, commands, and packages without fatal errors.
3. The test suite runs under `testing` and has no migration-introduced failures.
4. The frontend asset build succeeds.
5. Core clinical workflows and integrations listed above retain their existing contracts.
6. The branch contains reviewable checkpoint commits and no unrelated changes.

## Risks and mitigations

| Risk | Mitigation |
| --- | --- |
| Composer package conflict or unavailable package release | Resolve one major at a time; record the blocking package; upgrade, pin, or isolate it before proceeding. |
| Livewire major-version behavior changes | Upgrade as a dedicated compatibility step and use existing component tests plus focused regression tests. |
| Middleware order or CSRF behavior changes | Preserve ordering explicitly and test web, API, Livewire, and excluded routes. |
| Cache/session key changes invalidate active sessions or cached data | Review explicit prefixes and schedule a controlled operational cutover if the default must change. |
| Existing test environment is nondeterministic | Clear stale caches and make PHPUnit environment variables authoritative before judging migration results. |
| Network restrictions prevent Composer resolution | Stop at the last valid checkpoint and request approved network access only for Composer/package metadata. |
