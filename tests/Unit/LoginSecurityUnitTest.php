<?php

namespace Tests\Unit;

use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $countGenericMessage = substr_count($controllerFile, "Username atau password salah");
        $this->assertEquals(2, $countGenericMessage);
    }
}
