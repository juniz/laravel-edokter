# Task 5 Report: Laravel 12 → Laravel 13 Upgrade

Date: 2026-08-11
Branch: `codex/migrate-laravel-13`
Starting commit: `51fad621cf521eb2c2d4ec0ba59f0260c08f4700`

## Scope

Upgrade the application from Laravel 12.65.0 to Laravel 13, preserve the current application structure, avoid every database migration / rollback / refresh / seed command, verify the Laravel 13 boot path, and record any remaining non-DB-safe concerns.

## Context7 guidance used

Context7 Laravel 13 docs (`/laravel/docs/__branch__13.x`) confirmed:

- Laravel 13 requires PHP `8.3+`.
- Root dependency moves required by the upgrade guide:
  - `laravel/framework` → `^13.0`
  - `laravel/tinker` → `^3.0`
  - `phpunit/phpunit` → `^12.0`
- `VerifyCsrfToken` is renamed to `PreventRequestForgery` for direct middleware exclusions in tests / routes.
- Cache key prefixes, Redis prefixes, and session cookie names only change if the app relies on Laravel’s fallback defaults.

Repository review against that guidance found:

- No route or test code directly excluding the framework CSRF middleware class.
- Existing cache / redis / session config already pins the legacy underscore-based defaults explicitly in:
  - `config/cache.php`
  - `config/database.php`
  - `config/session.php`

Because those values are already explicit, no Laravel 13 cache/session prefix code change was required to preserve current behavior.

## Composer resolution and blockers

Initial root constraint updates:

- `php`: `^8.2` → `^8.3`
- `laravel/framework`: `^12.0` → `^13.0`
- `laravel/tinker`: `^2.9` → `^3.0`
- `phpunit/phpunit`: `^11.5` → `^12.0`

First Composer solver pass exposed one Laravel-13-incompatible root dependency:

- `yajra/laravel-datatables ^12.0` still required `illuminate/view ^12`

Package metadata inspection showed a Laravel 13 release line existed, so the root constraint was updated:

- `yajra/laravel-datatables`: `^12.0` → `^13.0`

`google-gemini-php/laravel` remained on `^2.0`, but Composer resolved it to `2.0.4`, the first release in that line that explicitly allows Laravel 13.

Composer resolution command used:

```bash
rtk composer update --with-all-dependencies --ignore-platform-req=ext-imagick
```

The narrow `--ignore-platform-req=ext-imagick` bypass remained necessary because the CLI environment still lacks `ext-imagick` while the application still depends on `spatie/pdf-to-image`.

## Effective dependency results

Key package outcomes after the Laravel 13 resolution:

- `laravel/framework` `v12.65.0` → `v13.24.0`
- `laravel/tinker` `v2.11.1` → `v3.0.2`
- `phpunit/phpunit` `11.5.56` → `12.5.33`
- `yajra/laravel-datatables` `v12.0.0` → `v13.0.0`
- `yajra/laravel-datatables-oracle` `v12.7.2` → `v13.1.6`
- `google-gemini-php/laravel` remained `2.0.4` and is Laravel 13-compatible
- `jeroennoten/laravel-adminlte` floated forward to `v3.16.0`
- `spatie/pdf-to-image` floated forward to `3.4.0`

Composer also reported:

- Lock file operations: `2 installs, 41 updates, 2 removals`
- `No security vulnerability advisories found.`

## Code and test-harness changes

No application runtime files under `app/`, `config/`, `bootstrap/`, or `routes/` needed Laravel 13 edits after package discovery, `artisan about`, and `artisan route:list` all succeeded on Laravel 13.

Two PHPUnit-12-specific harness changes were required:

1. `tests/Feature/SanitizeInputMiddlewareTest.php`
   - Converted legacy `/** @test */` docblock metadata to native `#[Test]` attributes so PHPUnit 12 discovers the tests again.
2. `phpunit.xml`
   - Migrated the deprecated PHPUnit XML schema to the current format using `vendor/bin/phpunit --migrate-configuration`.

Without the first change, `php artisan test tests/Feature/SanitizeInputMiddlewareTest.php --no-coverage` returned `No tests found.` under PHPUnit 12.

## Files changed

- `composer.json`
- `composer.lock`
- `phpunit.xml`
- `tests/Feature/SanitizeInputMiddlewareTest.php`

## Commands run and results

### Composer and upgrade resolution

- `rtk composer require php:^8.3 laravel/framework:^13.0 laravel/tinker:^3.0 --no-update`
  - Updated root runtime constraints.
- `rtk composer require --dev phpunit/phpunit:^12.0 --no-update`
  - Updated the PHPUnit floor.
- `rtk composer require yajra/laravel-datatables:^13.0 --no-update`
  - Corrected the Laravel-13-incompatible Yajra root constraint after Composer conflict output.
- `rtk composer show yajra/laravel-datatables --all`
  - Confirmed the `v13.0.0` line exists.
- `rtk composer show google-gemini-php/laravel --all`
  - Confirmed `2.0.4` explicitly supports Laravel 13.
- `rtk composer update --with-all-dependencies --ignore-platform-req=ext-imagick`
  - Succeeded and generated the Laravel 13 lockfile.
- `rtk composer validate --strict`
  - Passed.
- `rtk composer dump-autoload`
  - Passed.

### Laravel 13 verification

- `rtk php artisan package:discover --ansi`
  - Passed.
- `rtk php artisan about`
  - Passed.
  - Reported Laravel `13.24.0`, PHP `8.4.8`, Livewire `v3.8.4`, and Rappasoft Livewire Tables `v3.8.0`.
- `rtk php artisan route:list`
  - Passed.
  - Registered `116` routes.
- `rtk php artisan optimize:clear`
  - Passed.

### PHPUnit 12 harness compatibility

- `rtk proxy env APP_ENV=testing php artisan test tests/Feature/SanitizeInputMiddlewareTest.php --no-coverage`
  - Initially returned `No tests found.` because PHPUnit 12 no longer discovered the legacy docblock-only tests.
- `rtk proxy vendor/bin/phpunit --migrate-configuration`
  - Migrated `phpunit.xml` to the current schema.
- `rtk proxy env APP_ENV=testing php artisan test tests/Feature/SanitizeInputMiddlewareTest.php --no-coverage`
  - Passed after converting the test file to `#[Test]` attributes.

## Test verification

### Full suite

Command:

```bash
rtk proxy env APP_ENV=testing php artisan test --no-coverage
```

Result:

- Completed with the same sandbox-blocked MySQL failures seen in earlier tasks, not with Laravel 13 boot or routing regressions.
- Summary: `10 failed, 4 passed`.

Observed failing areas:

- `Tests\Feature\ExampleTest`
  - Requests `/`, which hits `LoginController@index` and performs a MySQL query.
- `Tests\Feature\PenandaanAreaOperasiTest`
  - Requires live MySQL access for schema / data checks and fails before exercising its assertions.

Representative error:

- `SQLSTATE[HY000] [2002] Operation not permitted (Connection: mysql, Host: localhost, Port: 3306, Database: sik, SQL: ...)`

### Focused non-DB suite

Command:

```bash
rtk proxy env APP_ENV=testing php artisan test tests/Unit/ExampleTest.php tests/Feature/LivewireThreeUpgradeTest.php tests/Feature/SanitizeInputMiddlewareTest.php tests/Feature/RouteListCommandTest.php --no-coverage
```

Result:

- Passed.
- Summary: `8 passed (29 assertions)`.

Passing focused coverage:

- `Tests\Unit\ExampleTest`
- `Tests\Feature\LivewireThreeUpgradeTest`
- `Tests\Feature\SanitizeInputMiddlewareTest`
- `Tests\Feature\RouteListCommandTest`

This verifies the Laravel 13 boot path, route registration, Livewire 3 configuration, sanitize middleware behavior, and PHPUnit 12 test discovery in the available non-database harness.

## Constraints honored

- No `migrate`, `migrate:fresh`, `migrate:refresh`, `migrate:rollback`, `db:seed`, or equivalent command was run.
- No database migration or seeding command was invoked indirectly.
- The only Composer platform bypass used was `--ignore-platform-req=ext-imagick`.

## Concerns / deferred follow-up

1. Full-suite verification remains limited by the same unavailable MySQL test environment; the remaining failures are connection failures, not Laravel 13 framework incompatibilities.
2. The application still depends on the `ext-imagick` bypass for Composer resolution in this CLI environment because of `spatie/pdf-to-image`.
3. No runtime code currently breaks on Laravel 13’s CSRF middleware rename, but the project still keeps its local middleware class named `VerifyCsrfToken`; that is acceptable for this checkpoint because Laravel 13 continues to honor the alias and no direct route/test exclusions needed updating.

## Outcome

Laravel 13 upgrade completed successfully through Composer, the repository now resolves on PHP `^8.3` with Laravel `^13.0`, Laravel 13 boot/package discovery/routes all pass, and the focused non-database verification suite is green again under PHPUnit 12. The only remaining failures are the already-known MySQL-backed tests that cannot run in this sandbox.
