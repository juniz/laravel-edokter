# Task 6 Report: CSRF / Framework Middleware Integration

Date: 2026-08-11
Branch: `codex/migrate-laravel-13`
Starting commit: `f04ed3706c2a6712ac4ec44efd3a477398a738ac`

## Scope

Implement only the Laravel 13 CSRF/framework middleware integration:

- add focused non-database regression coverage for the framework wiring
- rename the application CSRF middleware from `VerifyCsrfToken` to `PreventRequestForgery`
- switch the `web` middleware group and Sanctum config to the renamed class
- remove the old application middleware file

No database migration, rollback, refresh, or seeding command was run.

## Laravel 13 guidance used

Official Laravel 13 documentation confirmed:

- the framework middleware was renamed from `VerifyCsrfToken` to `PreventRequestForgery`
- the canonical Laravel 13 `web` middleware group uses `PreventRequestForgery`
- direct application references should be updated to the new class name

References reviewed:

- Laravel 13 upgrade guide — Request Forgery Protection
- Laravel 13 CSRF Protection docs
- Laravel 13 Middleware docs

## TDD / regression flow

1. Added `tests/Feature/FrameworkMiddlewareTest.php` first.
2. Ran the new focused test file before production changes.
3. Verified the expected red state:
   - the `web` middleware group still contained `App\Http\Middleware\VerifyCsrfToken`
   - `config('sanctum.middleware.verify_csrf_token')` still pointed at `App\Http\Middleware\VerifyCsrfToken`
4. Implemented the middleware rename and rewired framework references.
5. Re-ran the focused non-DB verification suite and confirmed green status.

## Files changed

- `app/Http/Kernel.php`
- `app/Http/Middleware/PreventRequestForgery.php`
- `app/Http/Middleware/VerifyCsrfToken.php` (removed)
- `config/sanctum.php`
- `tests/Feature/FrameworkMiddlewareTest.php`

## Implementation details

### `app/Http/Middleware/PreventRequestForgery.php`

- Added the new application middleware class.
- Extended `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`.
- Preserved the existing empty `$except` list.

### `app/Http/Kernel.php`

- Updated the `web` middleware group to use `\App\Http\Middleware\PreventRequestForgery::class`.

### `config/sanctum.php`

- Updated `middleware.verify_csrf_token` to use `App\Http\Middleware\PreventRequestForgery::class`.

### `app/Http/Middleware/VerifyCsrfToken.php`

- Removed the legacy application middleware file so the repository no longer keeps a first-party class on the old Laravel 12 name.

### `tests/Feature/FrameworkMiddlewareTest.php`

- Added focused assertions that:
  - the `web` middleware group contains `App\Http\Middleware\PreventRequestForgery`
  - Sanctum references the same application middleware class

## Verification

### Red check

Command:

```bash
rtk proxy env APP_ENV=testing php artisan test tests/Feature/FrameworkMiddlewareTest.php --no-coverage
```

Result:

- Failed as expected before the production change.
- Summary: `2 failed (2 assertions)`.

### Green checks

Command:

```bash
rtk proxy env APP_ENV=testing php artisan test tests/Feature/FrameworkMiddlewareTest.php tests/Feature/SanitizeInputMiddlewareTest.php --no-coverage
```

Result:

- Passed.
- Summary: `6 passed (10 assertions)`.

### Scope audit

Commands:

```bash
rtk rg -n -F "App\Http\Middleware\VerifyCsrfToken" .
rtk rg -n -F "Illuminate\Foundation\Http\Middleware\VerifyCsrfToken" .
```

Result:

- Both searches returned exit code `1`, confirming no remaining runtime source references to the old application class or old framework import remain in the repository.

## Constraints honored

- No `migrate`, `migrate:fresh`, `migrate:refresh`, `migrate:rollback`, `db:seed`, or equivalent command was run.
- Verification stayed within focused, non-database tests.
- No unrelated application behavior was changed.

## Deferred concerns

1. This task intentionally verified only the scoped middleware integration and the existing sanitize middleware regression path; it did not re-run the broader suite outside that scope.
2. If any external documentation or deployment notes still mention `App\Http\Middleware\VerifyCsrfToken`, they should be updated separately; no code references remain in this repository scan.

## Outcome

The application now uses a single Laravel 13-aligned CSRF middleware class, both the `web` middleware group and Sanctum point to that class, the legacy application middleware file has been removed, and the focused non-database regression suite is green.

---

## Fix round 1 — 2026-08-11

Review feedback identified one remaining Laravel 13 / Sanctum alignment gap and one test-hardening gap:

- `config/sanctum.php` still used the legacy compatibility key `verify_csrf_token`
- `FrameworkMiddlewareTest` only checked that `PreventRequestForgery` was present somewhere in the `web` group, not that it remained in the Laravel 13 slot/order

### Additional verification used for this round

Primary sources checked:

- installed package config stub: `vendor/laravel/sanctum/config/sanctum.php`
- installed package upgrade note: `vendor/laravel/sanctum/UPGRADE.md`
- installed runtime fallback logic: `vendor/laravel/sanctum/src/Http/Middleware/EnsureFrontendRequestsAreStateful.php`
- official Laravel upgrade guide section covering Sanctum 4 middleware keys

Those sources show:

- `validate_csrf_token` is Sanctum’s first-class config key
- `verify_csrf_token` remains only as a legacy fallback in Sanctum runtime resolution

### TDD follow-up

1. Tightened `FrameworkMiddlewareTest` first:
   - asserted the exact `web` middleware group sequence with `App\Http\Middleware\PreventRequestForgery` in the Laravel 13 CSRF slot
   - switched the Sanctum config assertion to `config('sanctum.middleware.validate_csrf_token')`
2. Ran the focused test file before changing config.
3. Verified red state:
   - `web` order assertion already passed
   - Sanctum assertion failed because `config('sanctum.middleware.validate_csrf_token')` returned `null`
4. Updated `config/sanctum.php` from `verify_csrf_token` to `validate_csrf_token`.
5. Re-ran the focused non-DB middleware regression suite.

### Files changed in fix round 1

- `config/sanctum.php`
- `tests/Feature/FrameworkMiddlewareTest.php`

### Fix-round verification

Red check:

```bash
rtk proxy env APP_ENV=testing php artisan test tests/Feature/FrameworkMiddlewareTest.php --no-coverage
```

Result:

- `1 failed, 1 passed`
- failure confirmed `config('sanctum.middleware.validate_csrf_token')` was `null` before the config change

Green check:

```bash
rtk proxy env APP_ENV=testing php artisan test tests/Feature/FrameworkMiddlewareTest.php tests/Feature/SanitizeInputMiddlewareTest.php --no-coverage
```

Result:

- passed
- summary: `6 passed (10 assertions)`

Repository audit for key usage:

```bash
rtk rg -n "validate_csrf_token|verify_csrf_token" config tests app vendor/laravel/sanctum/src/Http/Middleware/EnsureFrontendRequestsAreStateful.php
```

Result:

- application config now uses only `validate_csrf_token`
- test now reads only `validate_csrf_token`
- the only remaining `verify_csrf_token` reference in inspected sources is Sanctum’s vendor fallback logic for backward compatibility

### Constraints honored in fix round 1

- No `migrate`, `migrate:fresh`, `migrate:refresh`, `migrate:rollback`, `db:seed`, or equivalent command was run
- verification remained scoped to non-database middleware coverage
