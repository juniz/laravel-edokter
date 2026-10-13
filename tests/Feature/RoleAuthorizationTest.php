<?php

namespace Tests\Feature;

use App\Http\Middleware\RoleAuthorization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'role:admin'])->get('/_test/role-admin-only', function () {
            return response()->json(['status' => 'success']);
        });

        Route::middleware(['web', 'role:dokter,S004'])->get('/_test/role-dokter-or-spesialis', function () {
            return response()->json(['status' => 'success']);
        });
    }

    /** @test */
    public function it_redirects_unauthenticated_user_to_login()
    {
        $response = $this->get('/_test/role-admin-only');
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_denies_access_when_user_role_does_not_match()
    {
        $response = $this->withSession([
            'username' => 'D001',
            'role' => 'dokter',
            'kd_sps' => 'S0001',
        ])->get('/_test/role-admin-only');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_allows_access_when_user_role_matches()
    {
        $response = $this->withSession([
            'username' => 'D001',
            'role' => 'dokter',
            'kd_sps' => 'S0001',
        ])->get('/_test/role-dokter-or-spesialis');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    /** @test */
    public function it_allows_access_when_user_specialization_matches()
    {
        $response = $this->withSession([
            'username' => 'SP002',
            'role' => 'petugas',
            'kd_sps' => 'S004',
        ])->get('/_test/role-dokter-or-spesialis');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_always_allows_admin_access()
    {
        $response = $this->withSession([
            'username' => 'admin',
            'role' => 'admin',
        ])->get('/_test/role-admin-only');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_redirects_unauthenticated_guests_from_sensitive_clinical_routes()
    {
        $sensitiveRoutes = [
            '/persetujuan-penolakan-tindakan',
            '/odontogram/cetak',
            '/pegawai',
            '/echo-ar',
            '/notifications/get',
        ];

        foreach ($sensitiveRoutes as $uri) {
            $response = $this->get($uri);
            $response->assertRedirect(route('login'));
        }
    }
}
