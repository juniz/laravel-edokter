# Task 1 Report — Deterministic baseline and dependency inventory

## Outcome

Baseline captured successfully. The repository is still on Laravel 9.52.16, and the current test failures are environment/database access failures rather than Laravel 13 migration regressions.

No application code or test-harness files were changed.

## Files changed

- `.superpowers/sdd/2026-08-11-laravel-13-migration-plan/task-1-report.md`

The existing plan file at `docs/superpowers/plans/2026-08-11-laravel-13-migration-plan.md` was inspected and already present in the repository; it was not modified for this task.

## Dependency and framework inventory

Captured from `composer.json`, `composer.lock`, and `composer show --direct --locked`:

- PHP requirement: `^8.0.2`
- Laravel framework: `laravel/framework ^9.19` / locked `9.52.16`
- Sanctum: `^2.14.1` / locked `2.15.1`
- Tinker: `^2.7` / locked `2.8.2`
- Collision: `^6.1` / locked `6.4.0`
- Ignition: `^1.0` / locked `1.6.4`
- PHPUnit: `^9.5.10` / locked `9.6.15`
- Faker: `^1.9.1` / locked `1.23.0`
- Other notable direct packages present: Livewire 2.12.6, Laravel UI 3.4.6, Spatie PDF/Image tooling, Yajra DataTables, BotMan, PDF tooling, and admin template packages.

## Commands run and outcome

- `rtk php -v` — passed; PHP 8.4.8 available.
- `rtk composer validate --strict` — passed.
- `rtk composer show --direct --locked` — passed; direct package inventory captured.
- `rtk php artisan --version` — passed; Laravel Framework 9.52.16.
- `rtk git status --short --branch` — passed; clean branch `codex/migrate-laravel-13`.
- `rtk php artisan optimize:clear` — passed; Laravel caches cleared.
- `rtk proxy env APP_ENV=testing APP_DEBUG=true php artisan test --no-coverage` — failed; baseline failures recorded below.
- `rtk proxy env APP_ENV=testing APP_DEBUG=true php artisan test tests/Feature/ExampleTest.php --no-coverage --stop-on-failure` — failed with the same DB access issue.
- `rtk proxy env APP_ENV=testing APP_DEBUG=true php artisan test tests/Feature/PenandaanAreaOperasiTest.php --no-coverage --stop-on-failure` — failed with the same DB access issue.
- `rtk proxy env APP_ENV=testing php artisan test tests/Feature/SanitizeInputMiddlewareTest.php --no-coverage` — passed.

## Baseline failure classification

Classification: environment/database access failure.

The failures are caused by the test process attempting to use the application’s MySQL-backed configuration in an environment where the database is not reachable, not by a Laravel 13 upgrade regression.

### Failure 1: `Tests\Feature\ExampleTest`

- Failing test: `the application returns a successful response`
- First application exception: `PDOException: SQLSTATE[HY000] [2002] Operation not permitted`
- First application location: `app/Http/Controllers/LoginController.php:25`
- Triggered query: `select * from poliklinik where status = 1`

### Failure 2: `Tests\Feature\PenandaanAreaOperasiTest`

- Failing tests:
  - `penandaan table has required columns and unique visit`
  - `it persists a valid marking payload`
  - `it rejects an invalid diagram data url`
  - `it rejects a data url with empty decoded png bytes`
  - `it requires a logged in operator`
  - `it updates the existing visit instead of creating another row`
  - `it keeps markings isolated by visit and reloads a saved payload`
  - `print endpoint displays the saved marking`
  - `examination views mount the marking component with the current visit`
- First application exception: `Illuminate\Database\QueryException` wrapping `SQLSTATE[HY000] [2002] Operation not permitted`
- First application location observed from the stack trace: `tests/Feature/PenandaanAreaOperasiTest.php:93`
- Triggered query: `select * from information_schema.tables where table_schema = sik and table_name = dokter and table_type = 'BASE TABLE'`

### Passing focused baseline

- `Tests\Feature\SanitizeInputMiddlewareTest` passed all 4 tests.

## Harness diagnosis

The existing PHPUnit environment already sets:

- `APP_ENV=testing`
- `APP_DEBUG=true`
- `CACHE_DRIVER=array`
- `SESSION_DRIVER=array`
- `QUEUE_CONNECTION=sync`
- `DB_DATABASE=testing`

Because those values were already present and the failures are caused by unreachable MySQL access, no deterministic test-harness change was applied in `phpunit.xml`, `tests/TestCase.php`, or `tests/CreatesApplication.php`.

## Commit hashes

- Baseline branch head before this report: `490fd09`

No code commit was required for a harness fix; this task produced a report artifact only.

## Concerns

- PHPUnit emits extensive PHP 8.4 deprecation noise from Laravel 9 / Symfony / PHPUnit / PHPUnit-adjacent packages. The noise is not the first failure, but it will make later migration checkpoints harder to read unless suppressed or upgraded.
- The application’s default configuration still points at a MySQL database, so feature coverage that touches the database is not reproducible in the current sandbox without a reachable database service or a dedicated test database strategy.

## Fix round 1 correction

The reviewer was right: the initial report overstated the harness state by saying the checked-in PHPUnit harness already pinned `APP_DEBUG=true`. It did not. The command-line injection masked that gap.

### Files changed in the fix

- `phpunit.xml`
- `tests/CreatesApplication.php`

### Harness fix applied

- Added `APP_DEBUG=true` to the checked-in PHPUnit environment.
- Forced the test application bootstrap to normalize `APP_ENV=testing` before and after the Laravel bootstrap so the environment guard does not depend on ad hoc invocation-time injection.

### Commands run and outcome after the fix

- `rtk php artisan test tests/Feature/SanitizeInputMiddlewareTest.php --no-coverage` — passed, 4 tests.
- `rtk php artisan test tests/Feature/PenandaanAreaOperasiTest.php --no-coverage --stop-on-failure` — still failed, but now for the documented database access reason:
  - first failure reached the database guard in `tests/Feature/PenandaanAreaOperasiTest.php:93`
  - exception remained `SQLSTATE[HY000] [2002] Operation not permitted`

### Baseline failure classification after the fix

Still an environment/database access failure, not a Laravel 13 regression. The harness is now deterministic enough to surface the real database constraint instead of relying on invocation-time environment injection.

### Commit hashes

- Baseline report commit: `0760d92`
- Harness fix commit: `c9e7837`

### Remaining concerns

- The database-touching baseline still cannot run to completion in this sandbox because the application’s MySQL target is not reachable here.
- PHP 8.4 deprecation noise remains extensive and will continue to obscure future migration checkpoints until dependency upgrades reduce it.
