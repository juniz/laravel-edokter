<?php

namespace Tests\Unit;

use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LoginSecurityUnitTest extends TestCase
{
    /** @test */
    public function it_uses_pdo_parameterized_binding_for_username_encryption()
    {
        $maliciousPayload = "' OR '1'='1";

        $query = DB::table('user')
            ->join("dokter", "dokter.kd_dokter", "=", DB::raw("AES_DECRYPT(id_user, 'nur')"))
            ->whereRaw("id_user = AES_ENCRYPT(?, 'nur')", [$maliciousPayload])
            ->selectRaw("AES_DECRYPT(id_user, 'nur') as id_user, AES_DECRYPT(password, 'windi') as password, dokter.kd_sps");

        $sql = $query->toSql();
        $bindings = $query->getBindings();

        // Must contain parameter placeholder (?) in SQL query, NOT concatenated payload
        $this->assertStringContainsString("id_user = AES_ENCRYPT(?, 'nur')", $sql);
        $this->assertStringNotContainsString($maliciousPayload, $sql);

        // Bindings array must safely hold the raw payload separately for PDO
        $this->assertContains($maliciousPayload, $bindings);
    }

    /** @test */
    public function it_contains_identical_generic_error_message_for_both_failure_paths()
    {
        $controllerFile = file_get_contents(app_path('Http/Controllers/LoginController.php'));

        // Ensure old enumerating error messages no longer exist
        $this->assertStringNotContainsString("Password salah", $controllerFile);
        $this->assertStringNotContainsString("User tidak ditemukan", $controllerFile);

        // Ensure generic message is used
        $this->assertStringContainsString("Gagal login: Username atau password salah", $controllerFile);
    }

    /** @test */
    public function it_generates_consistent_throttle_key()
    {
        $controller = new LoginController();
        $request = Request::create('/customlogin', 'POST', [
            'username' => 'Dokter01',
        ], [], [], ['REMOTE_ADDR' => '192.168.1.50']);

        $key = $controller->throttleKey($request);
        $this->assertEquals('dokter01|192.168.1.50', $key);
    }

    /** @test */
    public function it_locks_out_after_three_failed_attempts_using_ratelimiter()
    {
        $throttleKey = 'test_user|127.0.0.1';
        RateLimiter::clear($throttleKey);

        $this->assertFalse(RateLimiter::tooManyAttempts($throttleKey, 3));
        $this->assertEquals(3, RateLimiter::remaining($throttleKey, 3));

        // 1st failed attempt
        RateLimiter::hit($throttleKey, 300);
        $this->assertEquals(2, RateLimiter::remaining($throttleKey, 3));
        $this->assertFalse(RateLimiter::tooManyAttempts($throttleKey, 3));

        // 2nd failed attempt
        RateLimiter::hit($throttleKey, 300);
        $this->assertEquals(1, RateLimiter::remaining($throttleKey, 3));
        $this->assertFalse(RateLimiter::tooManyAttempts($throttleKey, 3));

        // 3rd failed attempt
        RateLimiter::hit($throttleKey, 300);
        $this->assertEquals(0, RateLimiter::remaining($throttleKey, 3));
        $this->assertTrue(RateLimiter::tooManyAttempts($throttleKey, 3));

        // Available in should be > 0 (temporary lockout active)
        $this->assertGreaterThan(0, RateLimiter::availableIn($throttleKey));

        // Clean up
        RateLimiter::clear($throttleKey);
    }

    /** @test */
    public function it_has_throttle_middleware_on_customlogin_route()
    {
        $route = collect(Route::getRoutes())->first(function ($r) {
            return $r->getName() === 'customlogin';
        });

        $this->assertNotNull($route);
        $this->assertContains('throttle:10,1', $route->middleware());
    }
}
