<?php

namespace App\Http\Controllers\Domain\Provisioning;

use App\Application\Provisioning\TestServerConnectionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\Provisioning\ServerStoreRequest;
use App\Http\Requests\Domain\Provisioning\ServerUpdateRequest;
use App\Models\Domain\Provisioning\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;

class ServerController extends Controller
{
    public function index(): Response
    {
        $servers = Server::latest()->paginate(15);

        // Decrypt auth_secret_ref untuk display (hanya untuk admin)
        $servers->getCollection()->transform(function ($server) {
            try {
                $server->auth_secret_ref_display = Crypt::decryptString($server->auth_secret_ref);
            } catch (\Exception $e) {
                $server->auth_secret_ref_display = '***ENCRYPTED***';
            }

            return $server;
        });

        return Inertia::render('admin/servers/Index', [
            'servers' => $servers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/servers/Form');
    }

    public function store(ServerStoreRequest $request)
    {
        $data = $request->validated();

        // Enkripsi auth_secret_ref sebelum disimpan
        if (isset($data['auth_secret_ref'])) {
            $data['auth_secret_ref'] = Crypt::encryptString($data['auth_secret_ref']);
        }

        Server::create($data);

        return redirect()->route('admin.servers.index')
            ->with('success', 'Server berhasil dibuat.');
    }

    public function show(string $id): Response
    {
        $server = Server::find($id);

        if (! $server) {
            abort(404);
        }

        // Decrypt auth_secret_ref untuk display
        try {
            $server->auth_secret_ref_display = Crypt::decryptString($server->auth_secret_ref);
        } catch (\Exception $e) {
            $server->auth_secret_ref_display = '***ENCRYPTED***';
        }

        return Inertia::render('admin/servers/Show', [
            'server' => $server->load(['panelAccounts', 'provisionTasks']),
        ]);
    }

    public function edit(string $id): Response
    {
        $server = Server::find($id);

        if (! $server) {
            abort(404);
        }

        // Decrypt auth_secret_ref untuk form
        try {
            $server->auth_secret_ref = Crypt::decryptString($server->auth_secret_ref);
        } catch (\Exception $e) {
            $server->auth_secret_ref = '';
        }

        return Inertia::render('admin/servers/Form', [
            'server' => $server,
        ]);
    }

    public function update(ServerUpdateRequest $request, string $id)
    {
        $server = Server::find($id);

        if (! $server) {
            abort(404);
        }

        $data = $request->validated();

        // Enkripsi auth_secret_ref jika diubah (belum terenkripsi)
        if (isset($data['auth_secret_ref']) && ! empty($data['auth_secret_ref'])) {
            // Cek apakah sudah terenkripsi
            try {
                Crypt::decryptString($data['auth_secret_ref']);
                // Sudah terenkripsi, tidak perlu diubah
            } catch (\Exception $e) {
                // Belum terenkripsi, enkripsi sekarang
                $data['auth_secret_ref'] = Crypt::encryptString($data['auth_secret_ref']);
            }
        }

        $server->update($data);

        return redirect()->route('admin.servers.index')
            ->with('success', 'Server berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $server = Server::find($id);

        if (! $server) {
            abort(404);
        }

        $server->delete();

        return redirect()->route('admin.servers.index')
            ->with('success', 'Server berhasil dihapus.');
    }

    /**
     * Test connection ke server
     */
    public function testConnection(string $id, TestServerConnectionService $testService): JsonResponse
    {
        $server = Server::find($id);

        if (! $server) {
            return response()->json([
                'success' => false,
                'message' => 'Server tidak ditemukan',
            ], 404);
        }

        $result = $testService->execute($server);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
