<?php

namespace App\Http\Controllers\Domain\Subscription;

use App\Domain\Subscription\Contracts\SubscriptionRepository;
use App\Http\Controllers\Controller;
use App\Infrastructure\Provisioning\Adapters\AaPanelAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Subscription Controller
 *
 * ALUR BISNIS SUBSCRIPTION - Kapan Data Subscription Muncul di Halaman User:
 * ============================================================================
 *
 * 1. PEMBUATAN SUBSCRIPTION (Checkout Process)
 *    ------------------------------------------------------------
 *    Subscription dibuat saat customer melakukan checkout produk hosting/VPS:
 *
 *    a. Customer memilih produk dan plan di halaman catalog
 *    b. Customer melakukan checkout melalui:
 *       - CheckoutHostingService (untuk shared hosting)
 *       - CheckoutCatalogService (untuk produk catalog umum)
 *
 *    c. Saat checkout, sistem akan:
 *       - Membuat Order
 *       - Membuat Invoice
 *       - Membuat Subscription dengan status 'trialing' dan provisioning_status 'pending'
 *       - Membuat Payment record
 *       - Menghubungkan OrderItem dengan subscription_id
 *
 *    Status awal subscription:
 *    - status: 'trialing'
 *    - provisioning_status: 'pending'
 *    - start_at: sekarang
 *    - end_at: dihitung berdasarkan billing_cycle plan
 *    - next_renewal_at: sama dengan end_at
 *
 * 2. PEMBAYARAN (Payment Process)
 *    ------------------------------------------------------------
 *    Setelah customer melakukan pembayaran melalui Midtrans:
 *
 *    a. Customer membayar melalui payment gateway (Midtrans)
 *    b. Midtrans mengirim webhook ke aplikasi saat payment berhasil
 *    c. MidtransWebhookController menerima webhook
 *    d. MidtransAdapter memproses webhook dan update payment status menjadi 'succeeded'
 *    e. PaymentRepository.markAsSucceeded() dipanggil:
 *       - Update payment status menjadi 'succeeded'
 *       - Update invoice status menjadi 'paid'
 *       - Dispatch event InvoicePaid
 *
 * 3. PROVISIONING (Account Activation)
 *    ------------------------------------------------------------
 *    Setelah invoice dibayar, sistem akan melakukan provisioning:
 *
 *    a. Event InvoicePaid di-trigger
 *    b. ProvisionAccountOnInvoicePaid listener menangkap event
 *    c. Listener mencari OrderItem yang memiliki subscription_id
 *    d. Dispatch ProvisionAccountJob untuk setiap subscription
 *
 *    e. ProvisionAccountJob akan:
 *       - Update provisioning_status menjadi 'in_progress'
 *       - Menentukan server type berdasarkan product type
 *       - Mencari server aktif yang sesuai
 *       - Memanggil adapter (AaPanelAdapter, dll) untuk create account
 *       - Update subscription:
 *         * provisioning_status: 'done'
 *         * status: 'active' (dari 'trialing' menjadi 'active')
 *         * meta: menambahkan panel_account_id, username, server_id
 *
 * 4. SUBSCRIPTION MUNCUL DI HALAMAN USER
 *    ------------------------------------------------------------
 *    Subscription akan muncul di halaman user setelah:
 *
 *    a. Status subscription menjadi 'active' (setelah provisioning selesai)
 *    b. User login dan mengakses halaman subscriptions
 *    c. SubscriptionController.index() akan menampilkan semua subscription customer
 *
 *    Query untuk menampilkan subscription:
 *    - Customer route: findByCustomer($customer->id)
 *    - Admin route: dengan eager load product, plan, customer
 *
 * 5. SUBSCRIPTION CYCLE & BILLING HISTORY
 *    ------------------------------------------------------------
 *    Setiap periode billing akan membuat SubscriptionCycle:
 *
 *    a. Cycle pertama dibuat saat subscription dibuat (belum ada cycle record)
 *    b. Cycle berikutnya dibuat saat renewal:
 *       - RenewSubscriptionJob mencari subscription yang due for renewal
 *       - Generate invoice baru untuk renewal
 *       - Setelah payment berhasil, buat SubscriptionCycle baru
 *       - Update subscription dates (start_at, end_at, next_renewal_at)
 *
 *    c. SubscriptionCycle berisi:
 *       - cycle_no: nomor cycle (1, 2, 3, dst)
 *       - period_start: tanggal mulai periode
 *       - period_end: tanggal akhir periode
 *       - invoice_id: invoice untuk cycle tersebut
 *       - payment_id: payment yang membayar invoice
 *
 * 6. STATUS SUBSCRIPTION
 *    ------------------------------------------------------------
 *    Status yang mungkin:
 *    - 'trialing': Subscription baru dibuat, belum dibayar
 *    - 'active': Subscription aktif dan sudah di-provision
 *    - 'past_due': Pembayaran terlambat
 *    - 'suspended': Subscription di-suspend (bisa di-unsuspend)
 *    - 'cancelled': Subscription dibatalkan (tetap aktif sampai end_at)
 *    - 'terminated': Subscription dihentikan
 *
 * 7. RINGKASAN ALUR LENGKAP
 *    ------------------------------------------------------------
 *    1. Customer checkout → Subscription dibuat (status: trialing)
 *    2. Customer bayar → Payment webhook → Invoice paid
 *    3. InvoicePaid event → ProvisionAccountJob dispatched
 *    4. ProvisionAccountJob → Create panel account → Update status: active
 *    5. Subscription muncul di halaman user (status: active)
 *    6. Setiap renewal → Generate invoice → Payment → Create new cycle
 *
 * CATATAN PENTING:
 * - Subscription dibuat SEBELUM payment (saat checkout)
 * - Subscription diaktifkan SETELAH payment berhasil (melalui provisioning)
 * - Subscription hanya muncul di halaman user jika status = 'active'
 * - Subscription dengan status 'trialing' belum muncul karena belum di-provision
 */
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
