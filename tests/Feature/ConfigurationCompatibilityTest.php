<?php

namespace Tests\Feature;

use Tests\TestCase;

class ConfigurationCompatibilityTest extends TestCase
{
    public function test_cache_store_prefers_laravel_13_env_key_but_keeps_legacy_override_compatibility(): void
    {
        $this->withEnvironment([
            'CACHE_STORE' => null,
            'CACHE_DRIVER' => 'redis',
        ], function (): void {
            $config = $this->loadConfig('config/cache.php');

            $this->assertSame('redis', $config['default']);
        });

        $this->withEnvironment([
            'CACHE_STORE' => 'database',
            'CACHE_DRIVER' => 'redis',
        ], function (): void {
            $config = $this->loadConfig('config/cache.php');

            $this->assertSame('database', $config['default']);
        });
    }

    public function test_database_url_prefers_laravel_13_env_key_but_supports_legacy_database_url(): void
    {
        $this->withEnvironment([
            'DB_URL' => null,
            'DATABASE_URL' => 'mysql://legacy.test/app',
        ], function (): void {
            $config = $this->loadConfig('config/database.php');

            $this->assertSame('mysql://legacy.test/app', $config['connections']['mysql']['url']);
        });

        $this->withEnvironment([
            'DB_URL' => 'mysql://current.test/app',
            'DATABASE_URL' => 'mysql://legacy.test/app',
        ], function (): void {
            $config = $this->loadConfig('config/database.php');

            $this->assertSame('mysql://current.test/app', $config['connections']['mysql']['url']);
        });
    }

    public function test_default_cache_redis_and_session_prefixes_follow_laravel_13_hyphenated_fallbacks(): void
    {
        $this->withEnvironment([
            'APP_NAME' => 'E Dokter',
            'CACHE_PREFIX' => null,
            'REDIS_PREFIX' => null,
            'SESSION_COOKIE' => null,
        ], function (): void {
            $cache = $this->loadConfig('config/cache.php');
            $database = $this->loadConfig('config/database.php');
            $session = $this->loadConfig('config/session.php');

            $this->assertSame('e-dokter-cache-', $cache['prefix']);
            $this->assertSame('e-dokter-database-', $database['redis']['options']['prefix']);
            $this->assertSame('e-dokter-session', $session['cookie']);
        });
    }

    public function test_adminlte_config_exposes_current_asset_bundling_keys(): void
    {
        $config = $this->loadConfig('config/adminlte.php');

        $this->assertArrayHasKey('laravel_asset_bundling', $config);
        $this->assertArrayHasKey('laravel_css_path', $config);
        $this->assertArrayHasKey('laravel_js_path', $config);
        $this->assertFalse($config['laravel_asset_bundling']);
        $this->assertSame('resources/css/app.css', $config['laravel_css_path']);
        $this->assertSame('resources/js/app.js', $config['laravel_js_path']);
    }

    /**
     * @param  array<string, string|null>  $variables
     */
    private function withEnvironment(array $variables, callable $callback): void
    {
        $original = [];

        foreach ($variables as $key => $value) {
            $original[$key] = getenv($key);

            if ($value === null) {
                putenv($key);
                unset($_ENV[$key], $_SERVER[$key]);
            } else {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }

        try {
            $callback();
        } finally {
            foreach ($original as $key => $value) {
                if ($value === false) {
                    putenv($key);
                    unset($_ENV[$key], $_SERVER[$key]);
                } else {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    /**
     * @return array<mixed>
     */
    private function loadConfig(string $path): array
    {
        return require base_path($path);
    }
}
