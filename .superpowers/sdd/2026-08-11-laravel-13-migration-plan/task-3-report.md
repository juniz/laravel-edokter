# Task 3 Report — Laravel 10 to Laravel 11 Checkpoint

Status: DONE_WITH_CONCERNS

Date: 2026-08-11
Branch: `codex/migrate-laravel-13`
Base commit at report time: `da4a78f0612abc5b8996f5ead81b106740e646ec`

## Laravel 11 upgrade guidance used

- Context7 library: `/websites/laravel_11_x`
- Key guidance consulted:
  - Existing Laravel 10 applications may keep the legacy structure; adopting the new Laravel 11 skeleton is not required.
  - Laravel 11 requires PHP `8.2+`.
  - Update `laravel/framework` to `^11.0`.
  - Update `nunomaduro/collision` to the Laravel 11-compatible major.
  - If the application still uses `app/Http/Kernel.php`, prepend `Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class` to the global middleware stack.
  - Laravel 11 automatically rehashes passwords when needed; no repository-specific change was required because no custom password field handling was found during this task.
  - No repository-specific model cast or exception/bootstrap rewrite was required for the retained legacy structure.

## Composer blocker inspection

Initial Laravel 11 blocker tree after the Laravel 10 checkpoint showed that the following locked packages were tied to Laravel 10-era Illuminate ranges:

- `laravel/sanctum` `3.3.3`
- `livewire/livewire` `2.12.x`
- `jantinnerezo/livewire-alert` `2.2.x`
- `rappasoft/laravel-livewire-tables` `2.15.x`
- `yajra/laravel-datatables` `9.x`
- `barryvdh/laravel-snappy` `1.0.2`
- `nunomaduro/collision` `7.x`

Additional Composer package inspection confirmed Laravel 11-compatible target majors:

- `laravel/laravel v11.0.0` starter requirements: `php:^8.2`, `laravel/framework:^11.0`, `laravel/tinker:^2.9`, `nunomaduro/collision:^8.0`, `phpunit/phpunit:^10.5`, `spatie/laravel-ignition:^2.4`
- `laravel/sanctum` `4.x`
- `livewire/livewire` `3.8.4`
- `jantinnerezo/livewire-alert` `4.2.0`
- `rappasoft/laravel-livewire-tables` `3.8.0`
- `yajra/laravel-datatables` `11.0.0`
- `barryvdh/laravel-snappy` `1.0.5`

Platform verification after resolution:

- `rtk composer check-platform-reqs` reports only `ext-imagick` missing, from `spatie/pdf-to-image`.
- No additional platform requirement blocker was present.

## Package constraints changed

Retained `composer.json` constraint changes:

- `php`: `^8.1` → `^8.2`
- `barryvdh/laravel-snappy`: `^1.0` → `^1.0.5`
- `jantinnerezo/livewire-alert`: `^2.2` → `^4.0`
- `laravel/framework`: `^10.0` → `^11.0`
- `laravel/sanctum`: `^3.2` → `^4.0`
- `laravel/tinker`: `^2.8` → `^2.9`
- `livewire/livewire`: `^2.11` → `^3.8`
- `rappasoft/laravel-livewire-tables`: `^2.15` → `^3.0`
- `yajra/laravel-datatables`: `^9.0` → `^11.0`
- `fakerphp/faker`: `^1.9.1` → `^1.23`
- `mockery/mockery`: `^1.4.4` → `^1.6.12`
- `nunomaduro/collision`: `^7.0` → `^8.0`
- `phpunit/phpunit`: `^10.0` → `^10.5`
- `spatie/laravel-ignition`: `^2.0` → `^2.4`

Resolved lockfile versions:

- `laravel/framework` `11.55.0`
- `laravel/sanctum` `4.3.3`
- `laravel/tinker` `2.11.1`
- `laravel/ui` `4.6.3`
- `barryvdh/laravel-snappy` `1.0.5`
- `livewire/livewire` `3.8.4`
- `jantinnerezo/livewire-alert` `4.2.0`
- `rappasoft/laravel-livewire-tables` `3.8.0`
- `yajra/laravel-datatables` `11.0.0`
- `nunomaduro/collision` `8.5.0`
- `phpunit/phpunit` `10.5.64`
- `fakerphp/faker` `1.24.1`
- `spatie/laravel-ignition` `2.12.0`

Notable transitive lock changes tied to the upgrade:

- Symfony components moved to the Laravel 11-compatible `7.x` line
- `nunomaduro/termwind` `1.x` → `2.4.0`
- `laravel/prompts` `0.1.x` → `0.3.22`
- `nesbot/carbon` `2.x` → `3.13.2`
- Livewire 3 asset bundle was republished to `public/vendor/livewire/**`

## Application/API changes made

1. Added `Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class` to the top of `app/Http/Kernel.php` per the Laravel 11 upgrade guidance for legacy kernels.
2. Fixed `app/Http/Controllers/Ralan/RujukInternalPasien.php` so route registration and `php artisan route:list` no longer hard-fail on missing or non-encrypted `no_rawat` input:
   - preserved the raw `no_rawat` value for existing view usage
   - replaced constructor-time hard decrypt with safe decrypt semantics matching the existing `RequestDecryptMiddleware`
3. Added `tests/Feature/RouteListCommandTest.php` as a regression test for the `route:list` failure.

## Commands run and outcomes

Environment / baseline:

```bash
rtk git branch --show-current
rtk git rev-parse HEAD
```

- Outcome: branch `codex/migrate-laravel-13`, starting HEAD `da4a78f0612abc5b8996f5ead81b106740e646ec`

Context / inspection:

```bash
rtk composer prohibits laravel/framework 11.0 -t --locked
rtk composer show laravel/laravel v11.0.0 --all
rtk composer show livewire/livewire v3.8.4 --all
rtk composer show jantinnerezo/livewire-alert v4.2.0 --all
rtk composer show rappasoft/laravel-livewire-tables v3.8.0 --all
rtk composer show yajra/laravel-datatables v11.0.0 --all
rtk composer show barryvdh/laravel-snappy v1.0.5 --all
```

- Outcome: confirmed the Laravel 11-compatible majors listed above and identified the Laravel 10-bound blockers.

Constraint updates:

```bash
rtk composer require php:^8.2 laravel/framework:^11.0 laravel/sanctum:^4.0 laravel/tinker:^2.9 laravel/ui:^4.0 livewire/livewire:^3.8 jantinnerezo/livewire-alert:^4.0 rappasoft/laravel-livewire-tables:^3.0 yajra/laravel-datatables:^11.0 barryvdh/laravel-snappy:^1.0.5 --no-update
rtk proxy composer require --dev "fakerphp/faker:^1.23" "mockery/mockery:^1.6.12" "nunomaduro/collision:^8.0" "phpunit/phpunit:^10.5" "spatie/laravel-ignition:^2.4" --no-update -v
```

- Outcome: updated `composer.json` to the Laravel 11 checkpoint targets.

Resolution:

```bash
rtk composer update laravel/framework laravel/sanctum laravel/tinker laravel/ui livewire/livewire jantinnerezo/livewire-alert rappasoft/laravel-livewire-tables yajra/laravel-datatables barryvdh/laravel-snappy nunomaduro/collision spatie/laravel-ignition phpunit/phpunit fakerphp/faker mockery/mockery --with-all-dependencies --ignore-platform-req=ext-imagick
```

- Outcome: succeeded
- Notes:
  - upgraded the framework to Laravel `11.55.0`
  - moved the Livewire stack to `3.x`
  - moved the Yajra DataTables stack to `11.x`
  - republished Livewire assets through Composer scripts

App verification and debugging:

```bash
rtk php artisan package:discover --ansi
rtk php artisan about
rtk php artisan route:list
rtk php artisan optimize:clear
```

- Outcome: all four commands succeeded after the targeted `RujukInternalPasien` fix and Kernel middleware update

Regression verification:

```bash
rtk proxy env APP_ENV=testing php artisan test tests/Feature/RouteListCommandTest.php --no-coverage
```

- Outcome: passed

Full test pass required by the brief:

```bash
rtk proxy env APP_ENV=testing php artisan test --no-coverage
```

- Outcome: Laravel 11 test runner booted, but the suite did not complete cleanly in this sandbox
- Passing tests:
  - `Tests\Unit\ExampleTest`
  - `Tests\Feature\RouteListCommandTest`
  - `Tests\Feature\SanitizeInputMiddlewareTest`
- Failing tests:
  - `Tests\Feature\ExampleTest`
  - all `Tests\Feature\PenandaanAreaOperasiTest` cases
- Failure mode: all current failures are MySQL connection failures (`SQLSTATE[HY000] [2002] Operation not permitted`) against the unavailable sandbox database, consistent with the task brief’s existing limitation

Platform check:

```bash
rtk composer check-platform-reqs
```

- Outcome: only `ext-imagick` is missing; all other platform requirements are satisfied

Diff hygiene:

```bash
rtk proxy git diff --check
```

- Outcome: clean

## Files changed

- `app/Http/Controllers/Ralan/RujukInternalPasien.php`
- `app/Http/Kernel.php`
- `composer.json`
- `composer.lock`
- `public/vendor/livewire/livewire.js`
- `public/vendor/livewire/manifest.json`
- `public/vendor/livewire/livewire.esm.js`
- `public/vendor/livewire/livewire.esm.js.map`
- `public/vendor/livewire/livewire.min.js`
- `public/vendor/livewire/livewire.min.js.map`
- `tests/Feature/RouteListCommandTest.php`
- `.superpowers/sdd/2026-08-11-laravel-13-migration-plan/task-3-report.md`

## Tests / verification summary

- `php artisan package:discover` ✅
- `php artisan about` ✅
- `php artisan route:list` ✅
- `php artisan optimize:clear` ✅
- `php artisan test tests/Feature/RouteListCommandTest.php --no-coverage` ✅
- `php artisan test --no-coverage` ⚠️ blocked by the known MySQL sandbox limitation, not by a newly introduced Laravel 11 boot failure
- `composer check-platform-reqs` ⚠️ fails only for missing `ext-imagick`
- `git diff --check` ✅

## Commits

- Laravel 11 checkpoint: `7d25f7dabb491e8904b71e9a646ce0f061f252cf` (`build: upgrade application to Laravel 11`)

## Concerns

- Normal platform verification remains blocked by missing `ext-imagick`; the Laravel 11 Composer resolution required the narrow flag `--ignore-platform-req=ext-imagick` and `composer check-platform-reqs` still reports only that missing extension.
- The full PHPUnit run in this sandbox still cannot pass because the test suite depends on the unavailable MySQL-backed `sik` database.
- PHPUnit emits a deprecated XML schema warning during test execution; it is non-fatal and was not changed as part of this checkpoint.
