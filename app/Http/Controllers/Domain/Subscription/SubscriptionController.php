<?php

namespace App\Http\Controllers\Domain\Subscription;

use App\Domain\Subscription\Contracts\SubscriptionRepository;
use App\Http\Controllers\Controller;
use App\Infrastructure\Provisioning\Adapters\AaPanelAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository
    ) {}

    public function index(Request $request): Response
    {
        // Check if this is admin route
        if ($request->routeIs('admin.subscriptions.index')) {
            $subscriptions = \App\Models\Domain\Subscription\Subscription::with(['product', 'plan', 'customer'])
                ->latest()
                ->paginate(15);

            return Inertia::render('admin/subscriptions/Index', [
                'subscriptions' => $subscriptions,
            ]);
        }

        // Customer route
        $customer = $request->user()->customer;

        if (! $customer) {
            return Inertia::render('subscriptions/Index', [
                'subscriptions' => [],
            ]);
        }

        $subscriptions = $this->subscriptionRepository->findByCustomer($customer->id);

        return Inertia::render('subscriptions/Index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $subscription = $this->subscriptionRepository->findByUlid($id);

        if (! $subscription) {
            abort(404);
        }

        // Load relationships with cycles, invoices, and payments
        $subscription->load([
            'product',
            'plan',
            'customer',
            'cycles.invoice.payments',
            'panelAccount.server',
        ]);

        // Check if this is admin route
        if ($request->routeIs('admin.subscriptions.show')) {
            return Inertia::render('admin/subscriptions/Show', [
                'subscription' => $subscription,
            ]);
        }

        return Inertia::render('subscriptions/Show', [
            'subscription' => $subscription,
        ]);
    }

    public function cancel(Request $request, string $id)
    {
        $subscription = $this->subscriptionRepository->findByUlid($id);

        if (! $subscription) {
            abort(404);
        }

        $this->subscriptionRepository->updateStatus($subscription, 'cancelled');

        return redirect()->route('subscriptions.show', $id)
            ->with('success', 'Subscription berhasil dibatalkan.');
    }

    /**
     * Suspend panel account dari subscription
     */
    public function suspendPanelAccount(string $id): JsonResponse
    {
        $subscription = $this->subscriptionRepository->findByUlid($id);

        if (! $subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription tidak ditemukan'], 404);
        }

        $panelAccount = $subscription->panelAccount()->first();

        if (! $panelAccount) {
            return response()->json(['success' => false, 'message' => 'Panel account tidak ditemukan'], 404);
        }

        if (! $panelAccount->server) {
            return response()->json(['success' => false, 'message' => 'Server tidak ditemukan'], 404);
        }

        try {
            $adapter = new AaPanelAdapter;
            $adapter->suspendAccount($panelAccount);

            return response()->json([
                'success' => true,
                'message' => 'Panel account berhasil disuspend',
                'status' => 'suspended',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unsuspend panel account dari subscription
     */
    public function unsuspendPanelAccount(string $id): JsonResponse
    {
        $subscription = $this->subscriptionRepository->findByUlid($id);

        if (! $subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription tidak ditemukan'], 404);
        }

        $panelAccount = $subscription->panelAccount()->first();

        if (! $panelAccount) {
            return response()->json(['success' => false, 'message' => 'Panel account tidak ditemukan'], 404);
        }

        if (! $panelAccount->server) {
            return response()->json(['success' => false, 'message' => 'Server tidak ditemukan'], 404);
        }

        try {
            $adapter = new AaPanelAdapter;
            $adapter->unsuspendAccount($panelAccount);

            return response()->json([
                'success' => true,
                'message' => 'Panel account berhasil diaktifkan kembali',
                'status' => 'active',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get login URL untuk panel account dari subscription
     */
    public function getPanelLoginUrl(string $id): JsonResponse
    {
        $subscription = $this->subscriptionRepository->findByUlid($id);

        if (! $subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription tidak ditemukan'], 404);
        }

        $panelAccount = $subscription->panelAccount()->with('server')->first();

        if (! $panelAccount) {
            return response()->json(['success' => false, 'message' => 'Panel account tidak ditemukan'], 404);
        }

        if (! $panelAccount->server) {
            return response()->json(['success' => false, 'message' => 'Server tidak ditemukan'], 404);
        }

        try {
            $adapter = new AaPanelAdapter;
            $loginUrl = $adapter->getLoginUrl($panelAccount);

            return response()->json([
                'success' => true,
                'login_url' => $loginUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
