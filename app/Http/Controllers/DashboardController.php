<?php

namespace App\Http\Controllers;

use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Order\Order;
use App\Models\Domain\Subscription\Subscription;
use App\Models\Domain\Support\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        }

        return $this->customerDashboard($user);
    }

    private function adminDashboard(): Response
    {
        // Total statistics
        $totalUsers = User::count();
        $totalOrders = Order::count();
        $totalSubscriptions = Subscription::count();
        $totalInvoices = Invoice::count();

        // Revenue statistics
        $totalRevenue = Invoice::where('status', 'paid')
            ->sum('total_cents');
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_cents');

        // Order statistics
        $pendingOrders = Order::where('status', 'pending')->count();
        $paidOrders = Order::where('status', 'paid')->count();

        // Subscription statistics
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $expiredSubscriptions = Subscription::where('status', 'expired')->count();

        // Invoice statistics
        $unpaidInvoices = Invoice::where('status', 'unpaid')->count();
        $overdueInvoices = Invoice::where('status', 'overdue')->count();

        // Monthly revenue chart data (last 6 months)
        $monthlyRevenueData = Invoice::where('status', 'paid')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(total_cents) as revenue')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                $monthNames = [
                    'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec',
                ];

                return [
                    'name' => $monthNames[$item->month - 1],
                    'revenue' => $item->revenue / 100, // Convert cents to currency
                ];
            })
            ->values()
            ->toArray();

        // Orders per month (last 6 months)
        $monthlyOrdersData = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                $monthNames = [
                    'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec',
                ];

                return [
                    'name' => $monthNames[$item->month - 1],
                    'orders' => $item->count,
                ];
            })
            ->values()
            ->toArray();

        // Subscription status distribution
        $subscriptionStatusData = Subscription::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => ucfirst($item->status),
                    'value' => $item->count,
                ];
            })
            ->values()
            ->toArray();

        // Recent orders
        $recentOrders = Order::with(['customer.user'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'number' => $order->id,
                    'customer' => $order->customer->user->name ?? 'N/A',
                    'status' => $order->status,
                    'total' => $order->total_cents / 100,
                    'currency' => $order->currency,
                    'placed_at' => $order->placed_at?->format('Y-m-d H:i:s'),
                ];
            })
            ->values()
            ->toArray();

        // Pending invoices
        $pendingInvoices = Invoice::with(['customer.user'])
            ->whereIn('status', ['unpaid', 'overdue'])
            ->latest('due_at')
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'customer' => $invoice->customer->user->name ?? 'N/A',
                    'status' => $invoice->status,
                    'total' => $invoice->total_cents / 100,
                    'currency' => $invoice->currency,
                    'due_at' => $invoice->due_at?->format('Y-m-d'),
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('dashboard', [
            'role' => 'admin',
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalOrders' => $totalOrders,
                'totalSubscriptions' => $totalSubscriptions,
                'totalInvoices' => $totalInvoices,
                'totalRevenue' => $totalRevenue / 100,
                'monthlyRevenue' => $monthlyRevenue / 100,
                'pendingOrders' => $pendingOrders,
                'paidOrders' => $paidOrders,
                'activeSubscriptions' => $activeSubscriptions,
                'expiredSubscriptions' => $expiredSubscriptions,
                'unpaidInvoices' => $unpaidInvoices,
                'overdueInvoices' => $overdueInvoices,
            ],
            'charts' => [
                'monthlyRevenue' => $monthlyRevenueData,
                'monthlyOrders' => $monthlyOrdersData,
                'subscriptionStatus' => $subscriptionStatusData,
            ],
            'recentOrders' => $recentOrders,
            'pendingInvoices' => $pendingInvoices,
        ]);
    }

    private function customerDashboard(User $user): Response
    {
        $customer = $user->customer;

        if (! $customer) {
            return Inertia::render('dashboard', [
                'role' => 'customer',
                'stats' => [],
                'subscriptions' => [],
                'recentInvoices' => [],
                'recentTickets' => [],
                'domains' => [],
            ]);
        }

        // Active subscriptions
        $activeSubscriptions = Subscription::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->with(['product', 'plan'])
            ->latest('created_at')
            ->get()
            ->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'product' => $subscription->product->name ?? 'N/A',
                    'plan' => $subscription->plan->name ?? 'N/A',
                    'status' => $subscription->status,
                    'start_at' => $subscription->start_at?->format('Y-m-d'),
                    'end_at' => $subscription->end_at?->format('Y-m-d'),
                    'next_renewal_at' => $subscription->next_renewal_at?->format('Y-m-d'),
                    'auto_renew' => $subscription->auto_renew,
                ];
            })
            ->values()
            ->toArray();

        // Subscription statistics
        $totalSubscriptions = Subscription::where('customer_id', $customer->id)->count();
        $activeSubscriptionsCount = Subscription::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->count();
        $expiringSoon = Subscription::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->where('end_at', '<=', now()->addDays(30))
            ->where('end_at', '>', now())
            ->count();

        // Recent invoices
        $recentInvoices = Invoice::where('customer_id', $customer->id)
            ->with(['order'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'status' => $invoice->status,
                    'total' => $invoice->total_cents / 100,
                    'currency' => $invoice->currency,
                    'due_at' => $invoice->due_at?->format('Y-m-d'),
                    'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->values()
            ->toArray();

        // Unpaid invoices count
        $unpaidInvoicesCount = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->count();

        // Recent tickets
        $recentTickets = Ticket::where('customer_id', $customer->id)
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->values()
            ->toArray();

        // Open tickets count
        $openTicketsCount = Ticket::where('customer_id', $customer->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        // Domains count (from RDASH - placeholder, perlu implementasi)
        $domainsCount = 0; // TODO: Implementasi fetch dari RDASH API

        // Recent orders
        $recentOrders = Order::where('customer_id', $customer->id)
            ->with(['items.product', 'items.plan'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total' => $order->total_cents / 100,
                    'currency' => $order->currency,
                    'placed_at' => $order->placed_at?->format('Y-m-d H:i:s'),
                    'items_count' => $order->items->count(),
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('dashboard', [
            'role' => 'customer',
            'stats' => [
                'totalSubscriptions' => $totalSubscriptions,
                'activeSubscriptions' => $activeSubscriptionsCount,
                'expiringSoon' => $expiringSoon,
                'unpaidInvoices' => $unpaidInvoicesCount,
                'openTickets' => $openTicketsCount,
                'domains' => $domainsCount,
            ],
            'subscriptions' => $activeSubscriptions,
            'recentInvoices' => $recentInvoices,
            'recentTickets' => $recentTickets,
            'recentOrders' => $recentOrders,
        ]);
    }
}
