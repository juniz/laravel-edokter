# Dokumentasi Webhook Midtrans

## Overview

Sistem ini menggunakan Midtrans sebagai payment gateway dan menerima webhook notification untuk update status pembayaran secara real-time. Webhook ini memungkinkan sistem untuk secara otomatis memperbarui status pembayaran tanpa perlu polling manual.

## Endpoint Webhook

**URL:** `POST /api/payments/midtrans/webhook`

**Route Name:** `payments.midtrans.webhook`

**Authentication:** Tidak diperlukan (endpoint publik, diverifikasi via signature)

### Pengecekan URL Webhook

Untuk mengecek konfigurasi URL webhook dan konfigurasi yang diperlukan, jalankan command berikut:

```bash
php artisan midtrans:check-webhook-url
```

Command ini akan menampilkan:

- URL webhook yang benar berdasarkan `APP_URL`
- Status konfigurasi environment (server key, client key, dll)
- Instruksi setup di Midtrans Dashboard
- Cara testing webhook

## Konfigurasi

### 1. Setup di Midtrans Dashboard

1. Login ke [Midtrans Dashboard](https://dashboard.midtrans.com)
2. Masuk ke **Settings** > **Configuration**
3. Set **Payment Notification URL** ke:
   ```
   https://yourdomain.com/api/payments/midtrans/webhook
   ```
4. Pastikan **HTTP Notification** diaktifkan
5. Simpan konfigurasi

### 2. Konfigurasi di Aplikasi

Tambahkan konfigurasi berikut di file `.env`:

```env
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_VERIFY_WEBHOOK_SIGNATURE=true
```

**Catatan:**

- `MIDTRANS_VERIFY_WEBHOOK_SIGNATURE`: Set ke `true` untuk production (recommended), `false` untuk development/testing
- Signature verification memastikan webhook benar-benar berasal dari Midtrans

## Struktur Payload Webhook

Midtrans akan mengirim POST request dengan payload JSON berikut:

```json
{
	"transaction_time": "2024-01-15 10:30:00",
	"transaction_status": "settlement",
	"transaction_id": "abc123",
	"status_message": "midtrans payment notification",
	"status_code": "200",
	"signature_key": "abc123...",
	"payment_type": "credit_card",
	"order_id": "INV-INV-001-1234567890",
	"gross_amount": "100000.00",
	"fraud_status": "accept",
	"currency": "IDR",
	"settlement_time": "2024-01-15 10:30:00"
}
```

### Field Penting

| Field                | Deskripsi                                   | Contoh                                                         |
| -------------------- | ------------------------------------------- | -------------------------------------------------------------- |
| `order_id`           | Order ID yang digunakan saat create payment | `INV-INV-001-1234567890`                                       |
| `transaction_status` | Status transaksi                            | `settlement`, `pending`, `capture`, `deny`, `cancel`, `expire` |
| `fraud_status`       | Status fraud check (untuk credit card)      | `accept`, `challenge`, `deny`                                  |
| `payment_type`       | Tipe payment method                         | `credit_card`, `bank_transfer`, `gopay`, `qris`, dll           |
| `status_code`        | Status code response                        | `200`, `201`, `202`                                            |
| `status_message`     | Pesan status                                | `midtrans payment notification`                                |
| `gross_amount`       | Total amount                                | `100000.00`                                                    |
| `signature_key`      | Signature untuk verifikasi                  | `abc123...`                                                    |

## Status Transaksi

### Transaction Status

| Status           | Deskripsi                      | Action                                                            |
| ---------------- | ------------------------------ | ----------------------------------------------------------------- |
| `settlement`     | Payment berhasil               | Update payment status ke `succeeded`, mark invoice as paid        |
| `capture`        | Payment captured (credit card) | Update payment status ke `succeeded` jika fraud_status = `accept` |
| `pending`        | Menunggu pembayaran            | Update payment status ke `pending`                                |
| `deny`           | Payment ditolak                | Update payment status ke `failed`                                 |
| `cancel`         | Payment dibatalkan             | Update payment status ke `failed`                                 |
| `expire`         | Payment expired                | Update payment status ke `failed`                                 |
| `refund`         | Payment di-refund (full)       | Update payment status ke `refunded`                               |
| `partial_refund` | Payment di-refund (partial)    | Update payment status ke `refunded`                               |

### Fraud Status (Credit Card)

| Status      | Deskripsi            | Action                                    |
| ----------- | -------------------- | ----------------------------------------- |
| `accept`    | Payment aman         | Proses payment sebagai success            |
| `challenge` | Payment dalam review | Tetap status `pending`, tunggu konfirmasi |
| `deny`      | Payment ditolak      | Update status ke `failed`                 |

## Flow Processing

```
1. Midtrans mengirim webhook → POST /api/payments/midtrans/webhook
2. Controller menerima request
3. Validasi payload (order_id harus ada)
4. Simpan webhook log ke database
5. Verifikasi signature (jika enabled)
6. Cari payment berdasarkan order_id
7. Process webhook via MidtransAdapter
8. Update payment status sesuai transaction_status
9. Jika payment succeeded:
   - Mark payment as succeeded
   - Mark invoice as paid
   - Trigger InvoicePaid event
10. Update webhook log dengan hasil processing
11. Return response ke Midtrans
```

## Response Format

### Success Response

```json
{
	"success": true,
	"message": "Webhook processed successfully",
	"order_id": "INV-INV-001-1234567890"
}
```

**HTTP Status:** `200 OK`

### Error Response

#### Payment Not Found

```json
{
	"success": false,
	"message": "Payment not found"
}
```

**HTTP Status:** `200 OK` (agar Midtrans tidak retry terus)

#### Invalid Payload

```json
{
	"success": false,
	"message": "Invalid payload"
}
```

**HTTP Status:** `400 Bad Request`

#### Processing Error

```json
{
	"success": false,
	"message": "Webhook processing failed: [error message]"
}
```

**HTTP Status:** `500 Internal Server Error` (Midtrans akan retry)

## Webhook Logs

Semua webhook yang diterima akan disimpan di tabel `midtrans_webhook_logs` untuk tracking dan debugging.

### Struktur Table

| Column               | Type      | Deskripsi                                                    |
| -------------------- | --------- | ------------------------------------------------------------ |
| `id`                 | bigint    | Primary key                                                  |
| `order_id`           | string    | Order ID dari Midtrans                                       |
| `payment_id`         | char(26)  | ID payment di sistem (nullable)                              |
| `transaction_status` | string    | Status transaksi                                             |
| `fraud_status`       | string    | Fraud status (untuk credit card)                             |
| `payment_type`       | string    | Tipe payment method                                          |
| `status_code`        | string    | Status code                                                  |
| `status_message`     | string    | Pesan status                                                 |
| `processing_status`  | enum      | Status processing: `pending`, `success`, `failed`, `skipped` |
| `error_message`      | text      | Error message jika processing gagal                          |
| `payload`            | json      | Full webhook payload                                         |
| `response`           | json      | Response yang dikirim ke Midtrans                            |
| `ip_address`         | string    | IP address pengirim webhook                                  |
| `user_agent`         | string    | User agent pengirim webhook                                  |
| `created_at`         | timestamp | Waktu webhook diterima                                       |
| `updated_at`         | timestamp | Waktu terakhir diupdate                                      |

### Query Logs

```php
use App\Models\Domain\Billing\MidtransWebhookLog;

// Get all webhook logs
$logs = MidtransWebhookLog::all();

// Get logs by order_id
$logs = MidtransWebhookLog::where('order_id', 'INV-001')->get();

// Get failed webhooks
$failedLogs = MidtransWebhookLog::where('processing_status', 'failed')->get();

// Get logs with payment
$logsWithPayment = MidtransWebhookLog::with('payment')->get();
```

## Signature Verification

Webhook signature diverifikasi menggunakan algoritma SHA512:

```
signature = SHA512(order_id + status_code + gross_amount + server_key)
```

Signature verification dapat diaktifkan/nonaktifkan via config:

- `MIDTRANS_VERIFY_WEBHOOK_SIGNATURE=true` (production)
- `MIDTRANS_VERIFY_WEBHOOK_SIGNATURE=false` (development/testing)

## Idempotency

Sistem menggunakan idempotency check untuk mencegah duplicate processing:

- Jika payment sudah `succeeded`, webhook akan di-skip
- Webhook log tetap disimpan untuk tracking

## Testing

### 1. Testing dengan Midtrans Dashboard

1. Login ke Midtrans Dashboard
2. Masuk ke **Transactions**
3. Pilih transaction yang ingin di-test
4. Klik **Simulate Payment** atau **Simulate Notification**
5. Pilih status yang ingin di-simulate
6. Webhook akan dikirim ke endpoint yang dikonfigurasi

### 2. Testing dengan Postman/curl

```bash
curl -X POST https://yourdomain.com/api/payments/midtrans/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "transaction_time": "2024-01-15 10:30:00",
    "transaction_status": "settlement",
    "transaction_id": "test123",
    "status_message": "midtrans payment notification",
    "status_code": "200",
    "signature_key": "test_signature",
    "payment_type": "credit_card",
    "order_id": "INV-INV-001-1234567890",
    "gross_amount": "100000.00",
    "fraud_status": "accept",
    "currency": "IDR"
  }'
```

**Catatan:** Untuk testing, set `MIDTRANS_VERIFY_WEBHOOK_SIGNATURE=false` di `.env`

### 3. Testing dengan ngrok (Local Development)

1. Install ngrok: `brew install ngrok` atau download dari [ngrok.com](https://ngrok.com)
2. Start ngrok: `ngrok http 8000`
3. Copy HTTPS URL dari ngrok (contoh: `https://abc123.ngrok.io`)
4. Set webhook URL di Midtrans Dashboard: `https://abc123.ngrok.io/api/payments/midtrans/webhook`
5. Test payment dan webhook akan diterima di local

## Troubleshooting

### Webhook tidak diterima

1. **Cek konfigurasi URL di Midtrans Dashboard**

   - Pastikan URL benar dan dapat diakses publik
   - Pastikan menggunakan HTTPS untuk production

2. **Cek firewall/security**

   - Pastikan endpoint tidak di-block oleh firewall
   - Pastikan tidak ada IP whitelist yang memblokir Midtrans IP

3. **Cek logs**

   ```bash
   tail -f storage/logs/laravel.log | grep "Midtrans webhook"
   ```

4. **Cek webhook logs di database**
   ```php
   $logs = MidtransWebhookLog::latest()->take(10)->get();
   ```

### Payment tidak ter-update

1. **Cek webhook log**

   - Lihat `processing_status` di webhook log
   - Cek `error_message` jika ada error

2. **Cek payment status**

   - Pastikan payment dengan `order_id` yang sesuai ada di database
   - Cek apakah payment sudah `succeeded` (idempotency check)

3. **Cek signature verification**
   - Jika signature verification gagal, webhook akan di-reject
   - Set `MIDTRANS_VERIFY_WEBHOOK_SIGNATURE=false` untuk testing

### Signature verification failed

1. **Cek server_key**

   - Pastikan `MIDTRANS_SERVER_KEY` benar di `.env`
   - Pastikan menggunakan server_key yang sesuai dengan environment (sandbox/production)

2. **Cek payload**
   - Pastikan `order_id`, `status_code`, dan `gross_amount` ada di payload
   - Pastikan format sesuai dengan yang dikirim Midtrans

## Best Practices

1. **Selalu aktifkan signature verification di production**

   - Mencegah webhook dari sumber yang tidak terpercaya
   - Set `MIDTRANS_VERIFY_WEBHOOK_SIGNATURE=true`

2. **Monitor webhook logs secara berkala**

   - Cek webhook yang failed
   - Investigasi error yang terjadi

3. **Implement retry mechanism**

   - Midtrans akan otomatis retry jika response 500
   - Pastikan endpoint idempotent untuk menghindari duplicate processing

4. **Log semua webhook**

   - Semua webhook sudah otomatis di-log ke database
   - Gunakan untuk debugging dan audit trail

5. **Test webhook di staging sebelum production**
   - Pastikan webhook handler bekerja dengan benar
   - Test berbagai skenario (success, failed, pending, dll)

## Referensi

- [Midtrans Webhook Documentation](https://docs.midtrans.com/docs/core-api-status-code)
- [Midtrans Dashboard](https://dashboard.midtrans.com)
- [Midtrans API Reference](https://api-docs.midtrans.com/)

## Troubleshooting Error Production

### Error: Interface "PaymentRepository" not found

Jika terjadi error `Interface "App\Domain\Billing\Contracts\PaymentRepository" not found` di production:

1. **Pastikan file interface sudah ada:**

   ```bash
   ls -la app/Domain/Billing/Contracts/PaymentRepository.php
   ```

2. **Jalankan composer dump-autoload:**

   ```bash
   composer dump-autoload --optimize
   ```

3. **Clear cache Laravel:**

   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

4. **Pastikan file terdeploy dengan benar:**
   - File `app/Domain/Billing/Contracts/PaymentRepository.php` harus ada
   - File `app/Domain/Billing/Contracts/InvoiceRepository.php` tidak boleh berisi interface `PaymentRepository`

### Checklist Deployment

Sebelum deploy ke production, pastikan:

- [ ] File `app/Domain/Billing/Contracts/PaymentRepository.php` ada
- [ ] File `app/Domain/Billing/Contracts/InvoiceRepository.php` tidak berisi interface `PaymentRepository`
- [ ] Jalankan `composer dump-autoload --optimize`
- [ ] Clear semua cache Laravel
- [ ] Test webhook dengan curl atau Midtrans Dashboard

## Support

Jika ada masalah dengan webhook, silakan:

1. Cek webhook logs di database
2. Cek Laravel logs di `storage/logs/laravel.log`
3. Hubungi Midtrans support jika masalah terkait dengan Midtrans API
