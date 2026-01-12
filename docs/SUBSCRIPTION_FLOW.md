# Alur Bisnis Subscription

Dokumentasi lengkap tentang bagaimana subscription dibuat, diaktifkan, dan muncul di halaman user.

## 📋 Daftar Isi

1. [Ringkasan Alur](#ringkasan-alur)
2. [Pembuatan Subscription](#pembuatan-subscription)
3. [Proses Pembayaran](#proses-pembayaran)
4. [Provisioning Account](#provisioning-account)
5. [Subscription Muncul di Halaman User](#subscription-muncul-di-halaman-user)
6. [Subscription Cycle & Billing History](#subscription-cycle--billing-history)
7. [Status Subscription](#status-subscription)
8. [Renewal Process](#renewal-process)
9. [Diagram Alur](#diagram-alur)

---

## 🎯 Ringkasan Alur

```
1. Customer checkout → Subscription dibuat (status: trialing)
2. Customer bayar → Payment webhook → Invoice paid
3. InvoicePaid event → ProvisionAccountJob dispatched
4. ProvisionAccountJob → Create panel account → Update status: active
5. ✅ Subscription muncul di halaman user (status: active)
6. Setiap renewal → Generate invoice → Payment → Create new cycle
```

---

## 1️⃣ Pembuatan Subscription

### Kapan Subscription Dibuat?

Subscription dibuat saat customer melakukan **checkout** produk hosting/VPS melalui:

- **CheckoutHostingService** (untuk shared hosting)
- **CheckoutCatalogService** (untuk produk catalog umum)

### Proses Pembuatan

**File:** `app/Application/Hosting/CheckoutHostingService.php`

```php
// 1. Customer memilih produk dan plan
// 2. Customer melakukan checkout

// 3. Sistem membuat:
- Order (melalui PlaceOrderService)
- Invoice (melalui GenerateInvoiceService)
- Subscription dengan status awal:
  * status: 'trialing'
  * provisioning_status: 'pending'
  * start_at: sekarang
  * end_at: dihitung berdasarkan billing_cycle plan
  * next_renewal_at: sama dengan end_at
- Payment record (melalui PaymentAdapter)

// 4. Menghubungkan OrderItem dengan subscription_id
$order->items()->first()->update([
    'subscription_id' => $subscription->id,
]);
```

### Status Awal Subscription

| Field | Value | Keterangan |
|-------|-------|------------|
| `status` | `trialing` | Status awal, akan berubah menjadi `active` setelah provisioning |
| `provisioning_status` | `pending` | Menunggu payment untuk mulai provisioning |
| `start_at` | `now()` | Tanggal mulai subscription |
| `end_at` | `calculated` | Dihitung berdasarkan billing_cycle (monthly, annually, dll) |
| `next_renewal_at` | `end_at` | Tanggal renewal berikutnya |
| `auto_renew` | `true` | Default auto-renew aktif |

### Catatan Penting

⚠️ **Subscription dibuat SEBELUM payment** - Ini berarti subscription sudah ada di database meskipun belum dibayar.

---

## 2️⃣ Proses Pembayaran

### Alur Pembayaran

**File:** `app/Infrastructure/Payments/Adapters/MidtransAdapter.php`

```
1. Customer membayar melalui payment gateway (Midtrans)
   ↓
2. Midtrans mengirim webhook ke aplikasi saat payment berhasil
   ↓
3. MidtransWebhookController menerima webhook
   ↓
4. MidtransAdapter memproses webhook
   ↓
5. PaymentRepository.markAsSucceeded() dipanggil:
   - Update payment status menjadi 'succeeded'
   - Update invoice status menjadi 'paid'
   - Dispatch event InvoicePaid
```

### Webhook Processing

**File:** `app/Http/Controllers/Api/Payment/MidtransWebhookController.php`

```php
// Webhook diterima dari Midtrans
$payload = $request->all();

// Cari payment berdasarkan order_id
$payment = Payment::where('provider_ref', $payload['order_id'])->first();

// Handle webhook via adapter
$payment = $this->paymentAdapter->handleWebhook($payload);
```

### Payment Status Update

**File:** `app/Infrastructure/Persistence/Eloquent/PaymentRepository.php`

```php
public function markAsSucceeded(Payment $payment, array $payload): void
{
    $payment->update([
        'status' => 'succeeded',
        'paid_at' => now(),
        'raw_payload' => $payload,
    ]);

    // Load invoice dan mark as paid
    $invoice = $payment->invoice;
    if ($invoice && $invoice->status !== 'paid') {
        $this->invoiceRepository->markAsPaid($invoice);
        
        // Trigger InvoicePaid event
        Event::dispatch(new InvoicePaid($invoice));
    }
}
```

---

## 3️⃣ Provisioning Account

### Trigger Provisioning

**File:** `app/Listeners/ProvisionAccountOnInvoicePaid.php`

Setelah invoice dibayar, event `InvoicePaid` di-trigger dan listener akan:

```php
public function handle(InvoicePaid $event): void
{
    $invoice = $event->invoice;
    
    // Cari order items yang memiliki subscription
    if ($invoice->order) {
        $orderItems = OrderItem::where('order_id', $invoice->order->id)
            ->whereNotNull('subscription_id')
            ->get();
        
        foreach ($orderItems as $item) {
            if ($item->subscription_id) {
                // Dispatch job untuk provisioning
                ProvisionAccountJob::dispatch($item->subscription_id);
            }
        }
    }
}
```

### Provisioning Process

**File:** `app/Jobs/ProvisionAccountJob.php`

```php
public function handle(...): void
{
    // 1. Update provisioning_status menjadi 'in_progress'
    $subscription->update(['provisioning_status' => 'in_progress']);
    
    // 2. Determine server type berdasarkan product type
    $serverType = $this->determineServerType($subscription);
    // hosting_shared → 'aapanel'
    // vps → 'proxmox'
    
    // 3. Get server aktif yang sesuai
    $server = Server::where('type', $serverType)
        ->where('status', 'active')
        ->first();
    
    // 4. Resolve adapter berdasarkan server type
    $adapter = $adapterResolver->resolveByType($serverType);
    
    // 5. Create panel account
    $panelAccount = $adapter->createAccount($subscription, [
        'plan' => $subscription->plan->code,
        'domain' => $subscription->meta['domain'] ?? null,
        'server' => $server,
    ]);
    
    // 6. Update subscription
    $subscription->update([
        'provisioning_status' => 'done',
        'status' => 'active', // ✅ BERUBAH DARI 'trialing' KE 'active'
        'meta' => array_merge($subscription->meta ?? [], [
            'panel_account_id' => $panelAccount->id,
            'username' => $panelAccount->username,
            'server_id' => $server->id,
        ]),
    ]);
}
```

### Status Update

| Sebelum | Sesudah | Keterangan |
|---------|---------|------------|
| `provisioning_status: 'pending'` | `provisioning_status: 'done'` | Provisioning selesai |
| `status: 'trialing'` | `status: 'active'` | ✅ **Subscription aktif** |
| `meta: {...}` | `meta: {..., panel_account_id, username, server_id}` | Info panel account ditambahkan |

---

## 4️⃣ Subscription Muncul di Halaman User

### Kapan Subscription Muncul?

✅ **Subscription akan muncul di halaman user setelah:**

1. Status subscription menjadi `'active'` (setelah provisioning selesai)
2. User login dan mengakses halaman subscriptions
3. Query berhasil mengambil subscription customer

### Query Subscription

**File:** `app/Http/Controllers/Domain/Subscription/SubscriptionController.php`

```php
public function index(Request $request): Response
{
    // Customer route
    $customer = $request->user()->customer;
    
    if (! $customer) {
        return Inertia::render('subscriptions/Index', [
            'subscriptions' => [],
        ]);
    }
    
    // Query subscription customer
    $subscriptions = $this->subscriptionRepository->findByCustomer($customer->id);
    
    return Inertia::render('subscriptions/Index', [
        'subscriptions' => $subscriptions,
    ]);
}
```

### Filter Subscription

⚠️ **Catatan Penting:**

- Subscription dengan status `'trialing'` **BELUM muncul** karena belum di-provision
- Hanya subscription dengan status `'active'` yang muncul di halaman user
- Admin dapat melihat semua subscription termasuk yang `'trialing'`

### Tampilan di Halaman User

**File:** `resources/js/pages/subscriptions/Index.tsx`

User akan melihat:
- List semua subscription aktif mereka
- Informasi produk dan plan
- Status subscription
- Tanggal renewal
- Link ke detail subscription

---

## 5️⃣ Subscription Cycle & Billing History

### Apa itu Subscription Cycle?

Setiap periode billing akan membuat **SubscriptionCycle** yang berisi:
- `cycle_no`: Nomor cycle (1, 2, 3, dst)
- `period_start`: Tanggal mulai periode
- `period_end`: Tanggal akhir periode
- `invoice_id`: Invoice untuk cycle tersebut
- `payment_id`: Payment yang membayar invoice

### Cycle Pertama

Cycle pertama dibuat saat subscription dibuat (belum ada cycle record di database saat ini).

### Cycle Berikutnya (Renewal)

**File:** `app/Jobs/RenewSubscriptionJob.php`

```php
public function handle(...): void
{
    // 1. Cari subscription yang due for renewal
    $subscriptions = $subscriptionRepository->findDueForRenewal();
    
    foreach ($subscriptions as $subscription) {
        // 2. Generate invoice baru untuk renewal
        $invoice = $generateInvoiceService->execute([
            'customer_id' => $subscription->customer_id,
            'currency' => $subscription->plan->currency,
            'subtotal_cents' => $subscription->plan->price_cents,
            'total_cents' => $subscription->plan->price_cents,
            'due_at' => $subscription->next_renewal_at,
        ]);
        
        // 3. Setelah payment berhasil:
        //    - Buat SubscriptionCycle baru
        //    - Update subscription dates (start_at, end_at, next_renewal_at)
    }
}
```

### Billing History

Billing history ditampilkan di halaman detail subscription:

**File:** `resources/js/pages/admin/subscriptions/Show.tsx`

```tsx
// Get all cycles sorted by cycle_no
const allCycles = [...(subscription.cycles || [])].sort((a, b) => b.cycle_no - a.cycle_no);

// Tampilkan di table:
// - Cycle number
// - Period (start - end)
// - Invoice number
// - Amount
// - Status (paid/unpaid)
```

---

## 6️⃣ Status Subscription

### Daftar Status

| Status | Keterangan | Muncul di Halaman User? |
|--------|------------|-------------------------|
| `trialing` | Subscription baru dibuat, belum dibayar | ❌ Tidak |
| `active` | Subscription aktif dan sudah di-provision | ✅ **Ya** |
| `past_due` | Pembayaran terlambat | ✅ Ya |
| `suspended` | Subscription di-suspend (bisa di-unsuspend) | ✅ Ya |
| `cancelled` | Subscription dibatalkan (tetap aktif sampai end_at) | ✅ Ya |
| `terminated` | Subscription dihentikan | ✅ Ya |

### Transisi Status

```
trialing → active (setelah provisioning)
active → suspended (manual suspend)
suspended → active (unsuspend)
active → cancelled (customer cancel)
active → past_due (payment overdue)
past_due → active (payment success)
active → terminated (admin terminate)
```

### Provisioning Status

| Status | Keterangan |
|--------|------------|
| `pending` | Menunggu payment untuk mulai provisioning |
| `in_progress` | Sedang melakukan provisioning |
| `done` | Provisioning selesai |
| `failed` | Provisioning gagal |

---

## 7️⃣ Renewal Process

### Auto-Renewal

Jika `auto_renew = true`:

1. Sistem akan otomatis generate invoice saat `next_renewal_at` tiba
2. Charge payment gateway (jika payment method tersimpan)
3. Setelah payment berhasil:
   - Buat SubscriptionCycle baru
   - Update `start_at`, `end_at`, `next_renewal_at`
   - Update `status` jika perlu

### Manual Renewal

Jika `auto_renew = false` atau payment gagal:

1. Customer perlu melakukan pembayaran manual
2. Setelah payment berhasil, proses sama seperti auto-renewal

### Renewal Job

**File:** `app/Jobs/RenewSubscriptionJob.php`

Job ini dijalankan secara scheduled (cron) untuk:
- Mencari subscription yang due for renewal
- Generate invoice
- Charge payment (jika auto-renew aktif)

---

## 8️⃣ Diagram Alur

### Flowchart Lengkap

```
┌─────────────────────────────────────────────────────────────────┐
│                    CUSTOMER CHECKOUT                            │
│  Customer memilih produk & plan di catalog                     │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│              CHECKOUT SERVICE                                   │
│  • Create Order                                                 │
│  • Create Invoice                                               │
│  • Create Subscription (status: trialing)                      │
│  • Create Payment                                               │
│  • Link OrderItem → subscription_id                             │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│              CUSTOMER PAYMENT                                    │
│  Customer membayar melalui Midtrans                             │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│              MIDTRANS WEBHOOK                                    │
│  • Receive webhook                                               │
│  • Update payment status: succeeded                              │
│  • Update invoice status: paid                                    │
│  • Dispatch event: InvoicePaid                                   │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│              PROVISION ACCOUNT LISTENER                          │
│  • Listen InvoicePaid event                                       │
│  • Find OrderItem dengan subscription_id                         │
│  • Dispatch ProvisionAccountJob                                  │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│              PROVISION ACCOUNT JOB                               │
│  • Update provisioning_status: in_progress                      │
│  • Determine server type                                         │
│  • Get active server                                             │
│  • Create panel account via adapter                              │
│  • Update subscription:                                          │
│    - provisioning_status: done                                  │
│    - status: active ✅                                           │
│    - meta: add panel_account_id, username, server_id            │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│              SUBSCRIPTION MUNCUL DI HALAMAN USER                  │
│  • User login                                                    │
│  • Access /subscriptions                                         │
│  • Query subscription dengan status: active                      │
│  • ✅ Subscription ditampilkan                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Timeline

```
T0: Customer checkout
    ├─ Subscription dibuat (status: trialing)
    └─ Payment dibuat (status: pending)

T1: Customer bayar
    ├─ Payment webhook diterima
    ├─ Payment status: succeeded
    ├─ Invoice status: paid
    └─ Event InvoicePaid di-trigger

T2: Provisioning dimulai
    ├─ ProvisionAccountJob dispatched
    ├─ Provisioning status: in_progress
    └─ Panel account dibuat

T3: Provisioning selesai
    ├─ Provisioning status: done
    ├─ Subscription status: active ✅
    └─ Subscription muncul di halaman user

T4: Renewal (setelah end_at)
    ├─ Generate invoice baru
    ├─ Charge payment
    ├─ Create SubscriptionCycle baru
    └─ Update subscription dates
```

---

## 📝 Catatan Penting

### ⚠️ Poin Kritis

1. **Subscription dibuat SEBELUM payment**
   - Subscription sudah ada di database meskipun belum dibayar
   - Status awal: `trialing`

2. **Subscription diaktifkan SETELAH payment berhasil**
   - Melalui proses provisioning
   - Status berubah: `trialing` → `active`

3. **Subscription hanya muncul di halaman user jika status = 'active'**
   - Subscription dengan status `trialing` belum muncul
   - Admin dapat melihat semua subscription

4. **Provisioning adalah proses async**
   - Menggunakan queue job
   - Bisa memakan waktu beberapa detik hingga menit

5. **Billing history dibuat per cycle**
   - Setiap renewal membuat cycle baru
   - Cycle berisi invoice dan payment

### 🔍 Debugging Tips

Jika subscription tidak muncul di halaman user:

1. ✅ Cek status subscription: harus `'active'`
2. ✅ Cek provisioning_status: harus `'done'`
3. ✅ Cek apakah customer_id sesuai dengan user yang login
4. ✅ Cek log untuk error provisioning
5. ✅ Cek apakah payment sudah succeeded
6. ✅ Cek apakah InvoicePaid event sudah di-trigger

### 📚 File Terkait

- `app/Application/Hosting/CheckoutHostingService.php` - Checkout hosting
- `app/Application/Catalog/CheckoutCatalogService.php` - Checkout catalog
- `app/Infrastructure/Payments/Adapters/MidtransAdapter.php` - Payment adapter
- `app/Listeners/ProvisionAccountOnInvoicePaid.php` - Provision listener
- `app/Jobs/ProvisionAccountJob.php` - Provision job
- `app/Http/Controllers/Domain/Subscription/SubscriptionController.php` - Controller
- `app/Models/Domain/Subscription/Subscription.php` - Model
- `app/Models/Domain/Subscription/SubscriptionCycle.php` - Cycle model

---

## 🎓 Kesimpulan

Subscription dibuat saat checkout, diaktifkan setelah payment berhasil melalui proses provisioning, dan muncul di halaman user setelah status menjadi `active`. Proses ini melibatkan beberapa komponen: checkout service, payment gateway, webhook handler, event listener, dan queue job untuk provisioning.
