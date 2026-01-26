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
        // Snap API Endpoint
        $baseUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        // Load customer untuk mendapatkan data customer
        $invoice->load('customer');
        $customer = $invoice->customer;

        // Generate order ID
        $orderId = 'INV-' . $invoice->number . '-' . time();

        // Prepare transaction details
        $grossAmount = $invoice->total_cents;

        Log::info('Midtrans payment setup', [
            'invoice_id' => $invoice->id,
            'amount' => $grossAmount,
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
        $itemDetails = [];
        $itemsTotal = 0;
        foreach ($invoice->items as $item) {
            $itemPrice = $item->unit_price_cents ?? 0;
            $itemQuantity = $item->qty ?? 1;
            $itemTotal = $itemPrice * $itemQuantity;
            $itemsTotal += $itemTotal;

            $itemDetails[] = [
                'id' => $item->id,
                'price' => $itemPrice,
                'quantity' => $itemQuantity,
                // Midtrans max item name length is 50 chars
                'name' => substr($item->description ?? 'Item', 0, 50),
            ];
        }

        // Validate that sum of item_details equals gross_amount
        if (abs($itemsTotal - $grossAmount) > 0) {
            Log::warning('Midtrans item_details sum mismatch', [
                'invoice_id' => $invoice->id,
                'expected' => $grossAmount,
                'actual' => $itemsTotal,
            ]);

            // Adjust the last item to match gross_amount exactly
            if (! empty($itemDetails)) {
                $lastItemIndex = count($itemDetails) - 1;
                $adjustment = $grossAmount - $itemsTotal;
                $lastItemQuantity = $itemDetails[$lastItemIndex]['quantity'] ?? 1;
                $perUnitAdjustment = (int) round($adjustment / $lastItemQuantity);
                $itemDetails[$lastItemIndex]['price'] += $perUnitAdjustment;
            }
        }

        // Get payment method from options
        $paymentMethod = $options['payment_method'] ?? 'bca_va';

        // Prepare request payload untuk Midtrans Snap API
        $payload = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
            'enabled_payments' => $this->getEnabledPayments($paymentMethod),
            // 'credit_card' => ['secure' => true],
        ];

        // Call Midtrans Snap API
        try {
            Log::info('Midtrans Snap Request', [
                'url' => $baseUrl,
                'payload' => $payload,
            ]);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])->post($baseUrl, $payload);

            if (! $response->successful()) {
                Log::error('Midtrans Snap API HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payment_method' => $paymentMethod,
                ]);
                throw new \RuntimeException('Failed to create Midtrans payment: ' . $response->body());
            }

            $responseData = $response->json();

            // Validate response
            $token = $responseData['token'] ?? null;
            $redirectUrl = $responseData['redirect_url'] ?? null;

            if (! $token || ! $redirectUrl) {
                Log::error('Midtrans Snap API returned invalid response', [
                    'response' => $responseData,
                    'payment_method' => $paymentMethod,
                ]);
                throw new \RuntimeException('Midtrans payment failed: No token or redirect URL received.');
            }

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
                    'snap_token' => $token,
                    'redirect_url' => $redirectUrl,
                    'core_api_response' => $responseData, // Keeping key for compatibility
                ],
            ]);

            Log::info("Midtrans Snap payment created for invoice: {$invoice->id}", [
                'order_id' => $orderId,
                'payment_method' => $paymentMethod,
                'redirect_url' => $redirectUrl,
            ]);

            return $payment;
        } catch (\Exception $e) {
            Log::error("Midtrans payment creation failed for invoice: {$invoice->id}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get enabled payments array for Snap based on selected method
     */
    private function getEnabledPayments(string $paymentMethod): array
    {
        $map = [
            'credit_card' => ['credit_card'],
            'bca_va' => ['bca_va'],
            'bni_va' => ['bni_va'],
            'bri_va' => ['bri_va'],
            'mandiri_va' => ['echannel'], // Mandiri Bill Payment
            'permata_va' => ['permata_va'],
            'cimb_va' => ['cimb_va'],
            'danamon_va' => ['danamon_va'],
            'bsi_va' => ['other_va'], 
        ];

        return $map[$paymentMethod] ?? ['bca_va', 'bni_va', 'bri_va', 'echannel', 'permata_va']; // Default fallback to banks only
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
     * Check payment status directly from Midtrans API
     */
    public function checkStatus(Payment $payment): ?Payment
    {
        $serverKey = config('payment.midtrans.server_key');
        $isProduction = config('payment.midtrans.is_production', false);
        $baseUrl = $isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
            
        $orderId = $payment->provider_ref;
        
        if (!$orderId) {
            Log::warning('Cannot check status for payment without provider_ref', ['payment_id' => $payment->id]);
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])->get("{$baseUrl}/{$orderId}/status");

            if (!$response->successful()) {
                Log::error('Midtrans Status API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'order_id' => $orderId
                ]);
                
                // If 404, it might mean the transaction doesn't exist yet on Midtrans side (unlikely if we have provider_ref)
                return null;
            }

            $responseData = $response->json();
            
            // Map API response to webhook format to reuse handleWebhook logic
            // providing a synthetic payload
            return $this->handleWebhook($responseData);
            
        } catch (\Exception $e) {
            Log::error('Midtrans status check exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
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
        $signatureString = $orderId . $statusCode . $grossAmount . $serverKey;
        $expectedSignature = hash('sha512', $signatureString);

        $isValid = hash_equals($expectedSignature, $signature);

        if (! $isValid) {
            Log::warning('Midtrans webhook signature mismatch', [
                'order_id' => $orderId,
                'expected' => substr($expectedSignature, 0, 16) . '...',
                'received' => substr($signature, 0, 16) . '...',
            ]);
        }

        return $isValid;
    }
}
