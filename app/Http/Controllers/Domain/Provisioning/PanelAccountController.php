<?php

namespace App\Http\Controllers\Domain\Provisioning;

use App\Application\Provisioning\CreatePanelAccountService;
use App\Application\Provisioning\CreateVirtualAccountService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\Provisioning\PanelAccountCreateRequest;
use App\Http\Requests\Domain\Provisioning\VirtualAccountCreateRequest;
use App\Infrastructure\Provisioning\Adapters\AaPanelAdapter;
use App\Models\Domain\Provisioning\PanelAccount;
use App\Models\Domain\Provisioning\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PanelAccountController extends Controller
{
    public function index(Request $request): Response
    {
        $query = PanelAccount::with(['server', 'subscription.product', 'subscription.plan']);

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        $accounts = $query->latest()->paginate(15);

        // Get aaPanel servers untuk dropdown
        $aapanelServers = Server::where('type', 'aapanel')
            ->where('status', 'active')
            ->select('id', 'name', 'endpoint')
            ->get();

        return Inertia::render('admin/panel-accounts/Index', [
            'accounts' => $accounts,
            'filters' => $request->only('server_id'),
            'aapanelServers' => $aapanelServers,
        ]);
    }

    public function create(PanelAccountCreateRequest $request, CreatePanelAccountService $createService)
    {
        try {
            $data = $request->validated();
            $panelAccount = $createService->execute($data);

            return redirect()->route('admin.panel-accounts.show', $panelAccount->id)
                ->with('success', 'Panel account berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show(string $id): Response
    {
        $account = PanelAccount::with(['server', 'subscription.product', 'subscription.plan', 'subscription.customer'])
            ->find($id);

        if (! $account) {
            abort(404);
        }

        return Inertia::render('admin/panel-accounts/Show', [
            'account' => $account,
        ]);
    }

    public function createVirtualAccount(VirtualAccountCreateRequest $request, CreateVirtualAccountService $createService)
    {
        try {
            $data = $request->validated();
            $panelAccount = $createService->execute($data);

            return redirect()->route('admin.panel-accounts.show', $panelAccount->id)
                ->with('success', 'Virtual account berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Get packages dari aaPanel server
     */
    public function getServerPackages(Request $request): JsonResponse
    {
        $server = Server::find($request->server_id);

        if (! $server || $server->type !== 'aapanel') {
            return response()->json([
                'success' => false,
                'message' => 'Server tidak ditemukan atau bukan tipe aaPanel',
                'packages' => [],
            ]);
        }

        try {
            $adapter = new AaPanelAdapter;
            $packages = $adapter->getPackageList($server);

            return response()->json([
                'success' => true,
                'packages' => $packages,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'packages' => [],
            ]);
        }
    }

    /**
     * Get disk/mountpoints dari aaPanel server
     */
    public function getServerDisks(Request $request): JsonResponse
    {
        $server = Server::find($request->server_id);

        if (! $server || $server->type !== 'aapanel') {
            return response()->json([
                'success' => false,
                'message' => 'Server tidak ditemukan atau bukan tipe aaPanel',
                'disks' => [],
            ]);
        }

        try {
            $adapter = new AaPanelAdapter;
            $disks = $adapter->getDiskList($server);

            return response()->json([
                'success' => true,
                'disks' => $disks,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'disks' => [],
            ]);
        }
    }

    /**
     * Test connection ke aaPanel server
     */
    public function testServerConnection(Request $request): JsonResponse
    {
        $server = Server::find($request->server_id);

        if (! $server || $server->type !== 'aapanel') {
            return response()->json([
                'success' => false,
                'message' => 'Server tidak ditemukan atau bukan tipe aaPanel',
            ]);
        }

        try {
            $adapter = new AaPanelAdapter;
            $result = $adapter->testConnection($server);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get SSO login URL untuk user
     */
    public function getLoginUrl(string $id): JsonResponse
    {
        $account = PanelAccount::find($id);

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Account tidak ditemukan',
            ]);
        }

        try {
            $adapter = new AaPanelAdapter;
            $loginUrl = $adapter->getLoginUrl($account);

            return response()->json([
                'success' => true,
                'login_url' => $loginUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
