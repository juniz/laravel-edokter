<?php

namespace App\Http\Controllers\Domain\Provisioning;

use App\Http\Controllers\Controller;
use App\Models\Domain\Provisioning\PanelAccount;
use App\Models\Domain\Provisioning\ProvisionTask;
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

        return Inertia::render('admin/panel-accounts/Index', [
            'accounts' => $accounts,
            'filters' => $request->only('server_id'),
        ]);
    }

    public function show(string $id): Response
    {
        $account = PanelAccount::with(['server', 'subscription.product', 'subscription.plan', 'subscription.customer'])
            ->find($id);

        if (!$account) {
            abort(404);
        }

        return Inertia::render('admin/panel-accounts/Show', [
            'account' => $account,
        ]);
    }
}

