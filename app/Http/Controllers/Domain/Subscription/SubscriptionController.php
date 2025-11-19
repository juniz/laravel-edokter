<?php

namespace App\Http\Controllers\Domain\Subscription;

use App\Http\Controllers\Controller;
use App\Domain\Subscription\Contracts\SubscriptionRepository;
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
        
        if (!$customer) {
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

        if (!$subscription) {
            abort(404);
        }

        // Check if this is admin route
        if ($request->routeIs('admin.subscriptions.show')) {
            return Inertia::render('admin/subscriptions/Show', [
                'subscription' => $subscription->load(['product', 'plan', 'customer', 'cycles', 'panelAccount']),
            ]);
        }

        return Inertia::render('subscriptions/Show', [
            'subscription' => $subscription->load(['product', 'plan', 'customer', 'cycles', 'panelAccount']),
        ]);
    }

    public function cancel(Request $request, string $id)
    {
        $subscription = $this->subscriptionRepository->findByUlid($id);

        if (!$subscription) {
            abort(404);
        }

        $this->subscriptionRepository->updateStatus($subscription, 'cancelled');

        return redirect()->route('subscriptions.show', $id)
            ->with('success', 'Subscription berhasil dibatalkan.');
    }
}
