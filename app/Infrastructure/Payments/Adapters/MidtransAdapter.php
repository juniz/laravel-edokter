<?php

namespace App\Infrastructure\Payments\Adapters;

use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Domain\Billing\Contracts\PaymentRepository;
use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Billing\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransAdapter implements PaymentAdapterInterface
{
    public function __construct(
        private PaymentRepository $paymentRepository
    ) {}

    public function createCharge(Invoice $invoice, array $options): Payment
    {
        $serverKey = config('payment.midtrans.server_key');
        $isProduction = config('payment.midtrans.is_production', false);
        $baseUrl = $isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        // Load customer untuk mendapatkan data customer
        $invoice->load('customer');
        $customer = $invoice->customer;

        // Generate order ID
        $orderId = 'INV-'.$invoice->number.'-'.time();

        // Prepare transaction details
        // Midtrans expects gross_amount in rupiah (not cents)
        // Data di database sudah dalam format rupiah (meskipun field namanya _cents)
        // Jadi tidak perlu dibagi 100
        $grossAmount = $invoice->total_cents;

        Log::info('Midtrans payment amount conversion', [
            'invoice_id' => $invoice->id,
            'invoice_total_cents' => $invoice->total_cents,
            'gross_amount_sent' => $grossAmount,
            'currency' => $invoice->currency,
        ]);

        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
        ];

        // Prepare customer details
        $customerDetails = [
            'first_name' => $customer->name ?? 'Customer',
            'email' => $customer->email,
            'phone' => $customer->phone ?? '',
        ];

        // Prepare item details
        // Data sudah dalam format rupiah, tidak perlu dibagi 100
        $itemDetails = [];
        foreach ($invoice->items as $item) {
            $itemDetails[] = [
                'id' => $item->id,
                'price' => $item->unit_price_cents ?? 0,
                'quantity' => $item->qty ?? 1,
                'name' => $item->description ?? 'Item',
            ];
        }

        // Get payment method from options
        $paymentMethod = $options['payment_method'] ?? 'bca_va';

        // Prepare request payload untuk Midtrans Core API
        $payload = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        // Add payment method specific parameters berdasarkan Core API
        $this->addPaymentMethodParams($payload, $paymentMethod, $customer);

        // Call Midtrans Core API
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($serverKey.':'),
            ])->post("{$baseUrl}/charge", $payload);

            if (! $response->successful()) {
                Log::error('Midtrans Core API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payment_method' => $paymentMethod,
                ]);
                throw new \RuntimeException('Failed to create Midtrans payment: '.$response->body());
            }

            $responseData = $response->json();

            // Validate response - check if responseData is null or invalid
            if ($responseData === null || ! is_array($responseData)) {
                Log::error('Midtrans Core API returned invalid JSON response', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payment_method' => $paymentMethod,
                ]);
                throw new \RuntimeException('Failed to parse Midtrans payment response: Invalid JSON');
            }

            // Validate response status code
            // Midtrans API may return status_code as integer or string
            $statusCode = $responseData['status_code'] ?? null;
            $statusCodeNormalized = $statusCode !== null ? (string) $statusCode : null;

            if ($statusCodeNormalized !== '201') {
                Log::error('Midtrans Core API returned error', [
                    'status_code' => $statusCode,
                    'status_code_normalized' => $statusCodeNormalized,
                    'status_message' => $responseData['status_message'] ?? null,
                    'response' => $responseData,
                ]);
                throw new \RuntimeException('Midtrans payment failed: '.($responseData['status_message'] ?? 'Unknown error'));
            }

            // Extract payment information berdasarkan payment method
            $redirectUrl = $this->extractRedirectUrl($responseData, $paymentMethod);

            // Create payment record
            $payment = $this->paymentRepository->create([
                'invoice_id' => $invoice->id,
                'provider' => 'midtrans',
                'provider_ref' => $orderId,
                'amount_cents' => $invoice->total_cents,
                'status' => 'pending',
                'raw_payload' => [
                    'order_id' => $orderId,
                    'payment_method' => $paymentMethod,
                    'redirect_url' => $redirectUrl,
                    'core_api_response' => $responseData,
                ],
            ]);

            Log::info("Midtrans Core API payment created for invoice: {$invoice->id}", [
                'order_id' => $orderId,
                'payment_method' => $paymentMethod,
                'redirect_url' => $redirectUrl,
            ]);

            return $payment;
        } catch (\Exception $e) {
            Log::error("Midtrans Core API payment creation failed for invoice: {$invoice->id}", [
                'error' => $e->getMessage(),
                'payment_method' => $paymentMethod,
            ]);
            throw $e;
        }
    }

    /**
     * Add payment method specific parameters untuk Core API
     */
    private function addPaymentMethodParams(array &$payload, string $paymentMethod, $customer): void
    {
        switch ($paymentMethod) {
            case 'credit_card':
                $payload['payment_type'] = 'credit_card';
                // Credit card akan menggunakan 3DS jika diperlukan
                break;

            case 'bank_transfer':
                // Default bank transfer (akan dipilih oleh Midtrans)
                $payload['payment_type'] = 'bank_transfer';
                break;

            case 'bca_va':
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => 'bca'];
                break;

            case 'bni_va':
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => 'bni'];
                break;

            case 'bri_va':
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => 'bri'];
                break;

            case 'mandiri_va':
                $payload['payment_type'] = 'echannel';
                $payload['echannel'] = [
                    'bill_info1' => 'Payment:',
                    'bill_info2' => 'Online purchase',
                ];
                break;

            case 'permata_va':
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => 'permata'];
                break;

            case 'cimb_va':
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => 'cimb'];
                break;

            case 'danamon_va':
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => 'danamon'];
                break;

            case 'bsi_va':
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => 'bsi'];
                break;

            case 'qris':
                $payload['payment_type'] = 'qris';
                break;

            case 'gopay':
                $payload['payment_type'] = 'gopay';
                break;

            case 'shopeepay':
                $payload['payment_type'] = 'shopeepay';
                break;

            case 'dana':
                $payload['payment_type'] = 'dana';
                break;

            case 'ovo':
                $payload['payment_type'] = 'ovo';
                break;

            case 'linkaja':
                $payload['payment_type'] = 'linkaja';
                break;

            case 'cstore':
            case 'indomaret':
                $payload['payment_type'] = 'cstore';
                $payload['cstore'] = ['store' => 'indomaret'];
                break;

            case 'alfamart':
                $payload['payment_type'] = 'cstore';
                $payload['cstore'] = ['store' => 'alfamart'];
                break;

            default:
                // Default ke bank_transfer jika payment method tidak dikenali
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => 'bca'];
                break;
        }
    }

    /**
     * Extract redirect URL dari Core API response berdasarkan payment method
     */
    private function extractRedirectUrl(array $responseData, string $paymentMethod): ?string
    {
        // Credit card: redirect_url untuk 3DS
        if ($paymentMethod === 'credit_card' && isset($responseData['redirect_url'])) {
            return $responseData['redirect_url'];
        }

        // Bank transfer: tidak ada redirect_url, gunakan VA number
        if (str_starts_with($paymentMethod, 'bank_transfer') || str_ends_with($paymentMethod, '_va') || $paymentMethod === 'mandiri_va') {
            // VA number akan disimpan di raw_payload untuk ditampilkan ke user
            return null;
        }

        // E-Wallet: actions dengan deeplink atau QR code
        if (in_array($paymentMethod, ['gopay', 'shopeepay', 'dana', 'ovo', 'linkaja', 'qris'])) {
            if (isset($responseData['actions']) && is_array($responseData['actions'])) {
                foreach ($responseData['actions'] as $action) {
                    // Validate action structure
                    if (! is_array($action) || ! isset($action['name'])) {
                        continue;
                    }

                    if ($action['name'] === 'generate-qr-code' && isset($action['url']) && is_string($action['url'])) {
                        return $action['url'];
                    }
                    if ($action['name'] === 'deeplink-redirect' && isset($action['url']) && is_string($action['url'])) {
                        return $action['url'];
                    }
                }
            }
        }

        // Convenience Store: actions dengan payment code
        if (in_array($paymentMethod, ['cstore', 'indomaret', 'alfamart'])) {
            // Payment code akan disimpan di raw_payload untuk ditampilkan ke user
            return null;
        }

        return null;
    }

    public function handleWebhook(array $payload): ?Payment
    {
        $orderId = $payload['order_id'] ?? null;

        if (! $orderId) {
            Log::warning('Midtrans webhook received without order_id', ['payload' => $payload]);

            return null;
        }

        // Verify webhook signature untuk keamanan
        $shouldVerifySignature = config('payment.midtrans.verify_webhook_signature', true);
        if ($shouldVerifySignature) {
            $signature = $payload['signature_key'] ?? null;
            if (! $this->verifySignature($payload, $signature)) {
                Log::warning('Midtrans webhook signature verification failed', [
                    'order_id' => $orderId,
                    'payload' => $payload,
                ]);

                return null;
            }
        }

        $payment = Payment::where('provider_ref', $orderId)->first();

        if (! $payment) {
            Log::warning('Midtrans webhook payment not found', [
                'order_id' => $orderId,
                'payload' => $payload,
            ]);

            return null;
        }

        // Idempotency check: jika payment sudah succeeded, jangan proses lagi
        if ($payment->status === 'succeeded') {
            Log::info('Midtrans webhook: Payment already succeeded, skipping', [
                'order_id' => $orderId,
                'payment_id' => $payment->id,
            ]);

            return $payment;
        }

        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;

        Log::info('Midtrans webhook processing', [
            'order_id' => $orderId,
            'payment_id' => $payment->id,
            'transaction_status' => $transactionStatus,
            'fraud_status' => $fraudStatus,
            'payment_type' => $paymentType,
        ]);

        // Handle different transaction statuses berdasarkan dokumentasi Midtrans
        // https://docs.midtrans.com/docs/core-api-status-code
        switch ($transactionStatus) {
            case 'settlement':
                // Payment berhasil (settlement = payment success)
                // Untuk credit card, perlu cek fraud_status
                if ($paymentType === 'credit_card') {
                    if ($fraudStatus === 'accept') {
                        // Payment berhasil dan aman
                        $this->paymentRepository->markAsSucceeded($payment, $payload);
                        Log::info("Midtrans payment succeeded (settlement + fraud accept) for order: {$orderId}");
                    } elseif ($fraudStatus === 'challenge') {
                        // Payment dalam challenge (pending review)
                        $payment->update([
                            'status' => 'pending',
                            'raw_payload' => array_merge($payment->raw_payload ?? [], $payload),
                        ]);
                        Log::info("Midtrans payment in challenge for order: {$orderId}");
                    } else {
                        // Fraud status tidak diketahui atau deny
                        $payment->update([
                            'status' => 'pending',
                            'raw_payload' => array_merge($payment->raw_payload ?? [], $payload),
                        ]);
                        Log::warning("Midtrans payment settlement with unknown fraud_status for order: {$orderId}", [
                            'fraud_status' => $fraudStatus,
                        ]);
                    }
                } else {
                    // Untuk payment method selain credit card, settlement langsung berarti success
                    $this->paymentRepository->markAsSucceeded($payment, $payload);
                    Log::info("Midtrans payment succeeded (settlement) for order: {$orderId}");
                }
                break;

            case 'capture':
                // Credit card: payment captured (success)
                if ($fraudStatus === 'accept') {
                    $this->paymentRepository->markAsSucceeded($payment, $payload);
                    Log::info("Midtrans payment succeeded (capture + fraud accept) for order: {$orderId}");
                } elseif ($fraudStatus === 'challenge') {
                    $payment->update([
                        'status' => 'pending',
                        'raw_payload' => array_merge($payment->raw_payload ?? [], $payload),
                    ]);
                    Log::info("Midtrans payment in challenge (capture) for order: {$orderId}");
                } else {
                    $payment->update([
                        'status' => 'pending',
                        'raw_payload' => array_merge($payment->raw_payload ?? [], $payload),
                    ]);
                    Log::warning("Midtrans payment capture with unknown fraud_status for order: {$orderId}", [
                        'fraud_status' => $fraudStatus,
                    ]);
                }
                break;

            case 'pending':
                // Payment masih pending (menunggu pembayaran)
                $payment->update([
                    'status' => 'pending',
                    'raw_payload' => array_merge($payment->raw_payload ?? [], $payload),
                ]);
                Log::info("Midtrans payment still pending for order: {$orderId}");
                break;

            case 'deny':
                // Payment ditolak
                $payment->update([
                    'status' => 'failed',
                    'raw_payload' => array_merge($payment->raw_payload ?? [], $payload),
                ]);
                Log::info("Midtrans payment denied for order: {$orderId}");
                break;

            case 'cancel':
            case 'expire':
                // Payment dibatalkan atau expired
                $payment->update([
                    'status' => 'failed',
                    'raw_payload' => array_merge($payment->raw_payload ?? [], $payload),
                ]);
                Log::info("Midtrans payment {$transactionStatus} for order: {$orderId}");
                break;

            case 'refund':
            case 'partial_refund':
                // Payment di-refund (partial atau full)
                $payment->update([
                    'status' => 'refunded',
                    'raw_payload' => array_merge($payment->raw_payload ?? [], $payload),
                ]);
                Log::info("Midtrans payment refunded for order: {$orderId}", [
                    'refund_type' => $transactionStatus,
                ]);
                break;

            default:
                // Status tidak dikenali, update raw_payload saja
                $payment->update([
                    'raw_payload' => array_merge($payment->raw_payload ?? [], $payload),
                ]);
                Log::warning("Midtrans webhook received unknown transaction_status for order: {$orderId}", [
                    'transaction_status' => $transactionStatus,
                    'payload' => $payload,
                ]);
                break;
        }

        return $payment;
    }

    /**
     * Verify webhook signature untuk keamanan
     *
     * Midtrans menggunakan signature_key yang dihitung dari:
     * SHA512(order_id + status_code + gross_amount + server_key)
     */
    private function verifySignature(array $payload, ?string $signature): bool
    {
        if (! $signature) {
            Log::warning('Midtrans webhook signature missing', ['payload' => $payload]);

            return false;
        }

        $serverKey = config('payment.midtrans.server_key');

        if (! $serverKey) {
            Log::error('Midtrans server_key not configured');

            return false;
        }

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';

        // Hitung expected signature sesuai dokumentasi Midtrans
        $signatureString = $orderId.$statusCode.$grossAmount.$serverKey;
        $expectedSignature = hash('sha512', $signatureString);

        $isValid = hash_equals($expectedSignature, $signature);

        if (! $isValid) {
            Log::warning('Midtrans webhook signature mismatch', [
                'order_id' => $orderId,
                'expected' => substr($expectedSignature, 0, 16).'...',
                'received' => substr($signature, 0, 16).'...',
            ]);
        }

        return $isValid;
    }
}
