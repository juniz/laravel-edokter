<?php

namespace App\Http\Controllers\Domain\Provisioning;

use App\Http\Controllers\Controller;
use App\Models\Domain\Provisioning\Server;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServerController extends Controller
{
    public function index(): Response
    {
        $servers = Server::latest()->paginate(15);

        return Inertia::render('admin/servers/Index', [
            'servers' => $servers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/servers/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:cpanel,directadmin,proxmox'],
            'endpoint' => ['required', 'url'],
            'auth_secret_ref' => ['required', 'string'],
            'status' => ['required', 'in:active,maintenance,disabled'],
            'meta' => ['nullable', 'array'],
        ]);

        Server::create($validated);

        return redirect()->route('admin.servers.index')
            ->with('success', 'Server berhasil dibuat.');
    }

    public function show(string $id): Response
    {
        $server = Server::find($id);

        if (!$server) {
            abort(404);
        }

        return Inertia::render('admin/servers/Show', [
            'server' => $server->load(['panelAccounts', 'provisionTasks']),
        ]);
    }

    public function edit(string $id): Response
    {
        $server = Server::find($id);

        if (!$server) {
            abort(404);
        }

        return Inertia::render('admin/servers/Form', [
            'server' => $server,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $server = Server::find($id);

        if (!$server) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:cpanel,directadmin,proxmox'],
            'endpoint' => ['required', 'url'],
            'auth_secret_ref' => ['required', 'string'],
            'status' => ['required', 'in:active,maintenance,disabled'],
            'meta' => ['nullable', 'array'],
        ]);

        $server->update($validated);

        return redirect()->route('admin.servers.index')
            ->with('success', 'Server berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $server = Server::find($id);

        if (!$server) {
            abort(404);
        }

        $server->delete();

        return redirect()->route('admin.servers.index')
            ->with('success', 'Server berhasil dihapus.');
    }
}
