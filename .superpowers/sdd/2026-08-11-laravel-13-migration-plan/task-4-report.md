# Task 4 Report: Laravel 11 → Laravel 12 Upgrade

Date: 2026-08-11
Branch: `codex/migrate-laravel-13`
Starting commit: `51a3f3bf696fdd7778090a9821733dc2548cc8d0`

## Scope

Upgrade the application from Laravel 11.55.0 to Laravel 12, preserve the legacy application structure and existing Livewire 3 behavior, avoid database migration/seed commands entirely, and verify boot / route registration / package discovery / cache clearing / tests.

## Context7 guidance used

Context7 Laravel 12 docs (`/websites/laravel_12_x`) indicated:

- Laravel 12 is a relatively minor maintenance release with minimal breaking changes for most applications.
- Required dependency changes from Laravel 11 to 12:
  - `laravel/framework` → `^12.0`
  - `phpunit/phpunit` → `^11.0`
- The main low-likelihood breaking area called out was low-level database grammar / blueprint constructor changes for packages or apps extending those internals.

Repository scan found no custom grammar / blueprint subclasses or `setConnection()` usage under `app/`, `config/`, `bootstrap/`, `routes/`, `tests/`, or `database/`.

## Composer blocker inspection

`rtk composer prohibits laravel/framework 12.0 -t --locked` identified these direct blockers in the locked set:

- `botman/botman` `v2.8.3`
- `google-gemini-php/laravel` `1.0.1`
- `yajra/laravel-datatables` `v11.0.0` and its `yajra/*` subpackages
- `laravel/sail` `v1.29.0`
- `nunomaduro/collision` `v8.5.0`
- root package constraint `laravel/framework ^11.0`

Package metadata checks confirmed Laravel 12-capable releases existed for:

- `google-gemini-php/laravel` `2.0.4`
- `botman/botman` `2.8.11`
- `yajra/laravel-datatables` `12.0.0`
- `laravel/sail` `1.65.0`
- `nunomaduro/collision` `8.9.5`

`laravel/ui` and `spatie/laravel-ignition` were already locked on Laravel 12-compatible versions and did not require constraint changes.

## Dependency constraint changes

Updated root constraints:

- `laravel/framework`: `^11.0` → `^12.0`
- `google-gemini-php/laravel`: `^1.0` → `^2.0`
- `yajra/laravel-datatables`: `^11.0` → `^12.0`
- `laravel/sail`: `^1.29` → `^1.65`
- `nunomaduro/collision`: `^8.0` → `^8.9.5`
- `phpunit/phpunit`: `^10.5` → `^11.5`

Composer resolution command used:

```bash
rtk composer update laravel/framework google-gemini-php/laravel yajra/laravel-datatables botman/botman laravel/sail nunomaduro/collision phpunit/phpunit --with-all-dependencies --ignore-platform-req=ext-imagick
```

The narrow `--ignore-platform-req=ext-imagick` flag was required because the CLI environment does not have `ext-imagick`, while the repository still depends on `spatie/pdf-to-image`.

## Effective package results

Key resolved versions after the update:

- `laravel/framework` `v12.65.0`
- `google-gemini-php/laravel` `2.0.4`
- `botman/botman` `v2.8.11`
- `yajra/laravel-datatables` `v12.0.0`
- `yajra/laravel-datatables-buttons` `v12.3.1`
- `yajra/laravel-datatables-editor` `v12.3.0`
- `yajra/laravel-datatables-export` `v12.3.1`
- `yajra/laravel-datatables-fractal` `v12.0.1`
- `yajra/laravel-datatables-html` `v12.7.0`
- `yajra/laravel-datatables-oracle` `v12.7.2`
- `laravel/sail` `v1.65.0`
- `nunomaduro/collision` `v8.9.5`
- `phpunit/phpunit` `11.5.56`
- `livewire/livewire` remained `v3.8.4`
- `rappasoft/laravel-livewire-tables` remained `v3.8.0`

## Files changed

- `composer.json`
- `composer.lock`

No application code, configuration, route, bootstrap, or test source changes were required for Laravel 12 compatibility.

## Commands run and results

### Documentation / blocker inspection

- `rtk composer prohibits laravel/framework 12.0 -t --locked`
  - Confirmed the direct blockers listed above.
- Context7 Laravel 12 upgrade guidance queries
  - Confirmed Laravel 12 is a low-breakage maintenance upgrade and that `laravel/framework` / `phpunit` were the main required root dependency moves.

### Dependency update

- `rtk composer require laravel/framework:^12.0 google-gemini-php/laravel:^2.0 yajra/laravel-datatables:^12.0 --no-update`
  - Updated root runtime constraints.
- `rtk composer require --dev laravel/sail:^1.65 nunomaduro/collision:^8.9.5 phpunit/phpunit:^11.5 --no-update`
  - Updated root dev constraints.
- `rtk composer update ... --with-all-dependencies --ignore-platform-req=ext-imagick`
  - Succeeded.
  - Lock file operations: 2 installs, 43 updates, 0 removals.
  - Package discovery and vendor publishing completed successfully.

### Laravel verification

- `rtk composer dump-autoload`
  - Succeeded.
- `rtk php artisan package:discover --ansi`
  - Succeeded.
- `rtk php artisan about`
  - Succeeded.
  - Reported Laravel `12.65.0`, PHP `8.4.8`, Livewire `v3.8.4`, Rappasoft Livewire Tables `v3.8.0`.
- `rtk php artisan route:list`
  - Succeeded.
  - Registered `116` routes.
- `rtk php artisan optimize:clear`
  - Succeeded.

## Test verification

### Full suite

Command:

```bash
rtk proxy env APP_ENV=testing php artisan test --no-coverage
```

Result:

- Completed with failures, but the failures were environment-gated MySQL connection errors rather than Laravel 12 upgrade regressions.
- Summary: `10 failed, 8 passed`.

Failing areas:

- `Tests\Feature\ExampleTest`
  - Requests `/`, which hits `LoginController@index` and performs a MySQL query against `poliklinik`.
- `Tests\Feature\PenandaanAreaOperasiTest`
  - Requires live MySQL access and fails on connection attempts during table / record checks.

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

This confirms the Laravel 12 boot path, Livewire 3 configuration, route-list command behavior, and sanitize middleware behavior stayed intact in the available non-DB harness.

## Concerns

1. Full-suite verification remains limited by the known unavailable MySQL-backed test environment. The remaining failures were connection failures, not framework-upgrade exceptions.
2. PHPUnit 11 now warns that:
   - `phpunit.xml` uses a deprecated schema
   - `SanitizeInputMiddlewareTest` still uses doc-comment metadata that will need attributes before PHPUnit 12
   These are warnings only and were not changed in this task because they are not Laravel 12 blockers.
3. Composer reported: `Found 6 security vulnerability advisories affecting 1 package.` I did not investigate or remediate those advisories in this migration checkpoint because that is outside this scoped framework upgrade.

## Outcome

Laravel 12 upgrade completed successfully through Composer with no application code changes required. The app boots, package discovery succeeds, routes register, caches clear, and the focused non-database verification suite passes on Laravel 12. The only remaining red status is the already-known MySQL-backed test unavailability.

## Fix Round 1: PHP 8.3 Portability and Report Corrections

Fix-round starting commit: `fae5a183e27710dacff1a2733cdfb5b876dbd710`
Fix-round checkpoint commit: `43c76735d73802ed7b4a7ba8d3da99d36b46ebfc`

### Finding addressed

The first Laravel 12 checkpoint locked `symfony/yaml` `v8.1.2`, which requires `php >=8.4.1`. The portability review correctly flagged that this made the lock unsuitable for the migration target PHP `^8.3`.

### Root cause

Initial investigation:

- `rtk composer why symfony/yaml`
  - showed `laravel/sail v1.65.0` allowed `symfony/yaml ^6.0|^7.0|^8.0`
- `rtk composer show symfony/yaml --locked`
  - confirmed the locked package was `v8.1.2` with `php >=8.4.1`

Further lock analysis showed the broader cause was not Sail alone. The lock had been resolved on a local PHP `8.4.8` interpreter, so Composer had also selected other PHP-8.4-only Symfony 8 components where package constraints allowed them.

Composer proof after investigation:

- `rtk composer prohibits php 8.3 --locked -t`
  - identified PHP-8.3 blockers in the locked graph through:
    - `symfony/clock`
    - `symfony/css-selector`
    - `symfony/event-dispatcher`
    - `symfony/string`
    - `symfony/translation`

Direct ownership traces:

- `rtk composer why symfony/clock --locked`
  - `nesbot/carbon` required `symfony/clock ^6.3.12 || ^7.0 || ^8.0`
- `rtk composer why symfony/css-selector --locked`
  - `tijsverkoyen/css-to-inline-styles` required `symfony/css-selector ^5.4 || ^6.0 || ^7.0 || ^8.0`
- `rtk composer why symfony/event-dispatcher --locked`
  - `symfony/http-kernel` / `symfony/mailer` allowed `^8.0`
- `rtk composer why symfony/string --locked`
  - `symfony/console` allowed `^7.2|^8.0`
- `rtk composer why symfony/translation --locked`
  - `nesbot/carbon` and `symfony/http-kernel` allowed `^8.0`

### Fix applied

Instead of keeping a one-off direct `symfony/yaml` pin, the dependency graph was corrected at the selection boundary by telling Composer to resolve for the migration target platform:

```bash
rtk composer config platform.php 8.3.0
```

Then the affected graph was re-resolved:

```bash
rtk composer update laravel/framework laravel/sail --with-all-dependencies --ignore-platform-req=ext-imagick
```

Finally the lock metadata was refreshed after removing the temporary direct `symfony/yaml` root entry:

```bash
rtk composer update --lock --ignore-platform-req=ext-imagick
```

### Effective lockfile changes in fix round

Composer downgraded the PHP-8.4-only Symfony packages back to PHP-8.3-compatible Symfony 7.4 releases:

- `symfony/yaml` `v8.1.2` → `v7.4.15`
- `symfony/clock` `v8.1.0` → `v7.4.8`
- `symfony/css-selector` `v8.1.0` → `v7.4.9`
- `symfony/event-dispatcher` `v8.1.2` → `v7.4.15`
- `symfony/string` `v8.1.2` → `v7.4.15`
- `symfony/translation` `v8.1.4` → `v7.4.16`

`composer.json` now includes:

```json
"config": {
    "platform": {
        "php": "8.3.0"
    }
}
```

No direct `symfony/yaml` requirement remains in the root package.

### PHP 8.3 compatibility verification

Final Composer compatibility check:

```bash
rtk composer prohibits php 8.3 --locked
```

Result:

- `Package "php 8.3" found in version "8.3.0" (version provided by config.platform).`
- `There is no installed package depending on "php" in versions not matching 8.3`

This verifies the final synced lockfile has no PHP 8.3 blockers.

### Final verification rerun after fix round

- `rtk php artisan package:discover --ansi`
  - passed
- `rtk php artisan about`
  - passed
  - Laravel `12.65.0`, Livewire `v3.8.4`, Rappasoft Livewire Tables `v3.8.0`
- `rtk php artisan route:list`
  - passed
  - `116` routes registered
- `rtk proxy env APP_ENV=testing php artisan test tests/Unit/ExampleTest.php tests/Feature/LivewireThreeUpgradeTest.php tests/Feature/SanitizeInputMiddlewareTest.php tests/Feature/RouteListCommandTest.php --no-coverage`
  - passed
  - `8 passed (29 assertions)`

### Remaining concerns after fix round

1. Composer resolution still required the pre-approved narrow `--ignore-platform-req=ext-imagick` because the CLI environment lacks `ext-imagick` while the repository still depends on `spatie/pdf-to-image`.
2. MySQL-backed feature tests remain unavailable in this environment, so the known full-suite MySQL connection failures still cannot be cleared here without external database access.
3. Composer still reports `6` security vulnerability advisories affecting `1` package. That remains outside the scope of this Laravel 12 checkpoint.
4. PHPUnit 11 still emits warnings about deprecated XML schema metadata and doc-comment metadata in `SanitizeInputMiddlewareTest`; these are warnings only and were not changed in this task.

### Fix Round 2: Report command correction

- Corrected the remaining bare Composer example in the PHP 8.3 compatibility section to the actual command form used in this task: `rtk composer prohibits php 8.3 --locked`.
- No code, dependency, or database-related changes were made in this round.

### Fix Round 3: Report command sweep

- Searched the full report for remaining bare Composer command examples.
- Corrected the blocker-tree command to the actual command form used in this task: `rtk composer prohibits php 8.3 --locked -t`.
- No code, dependency, or database-related changes were made in this round.
