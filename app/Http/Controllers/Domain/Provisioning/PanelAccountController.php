<?php

namespace App\Http\Controllers\Domain\Provisioning;

use App\Application\Provisioning\CreatePanelAccountService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\Provisioning\PanelAccountCreateRequest;
use App\Models\Domain\Provisioning\PanelAccount;
use App\Models\Domain\Provisioning\Server;
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
}
