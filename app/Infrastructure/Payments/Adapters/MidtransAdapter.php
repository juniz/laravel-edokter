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
            ? 'https://app.midtrans.com/v2'
            : 'https://app.sandbox.midtrans.com/v2';

        // Load customer untuk mendapatkan data customer
        $invoice->load('customer');
        $customer = $invoice->customer;

        // Generate order ID
        $orderId = 'INV-' . $invoice->number . '-' . time();

        // Prepare transaction details
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => $invoice->total_cents / 100, // Convert cents to amount
        ];

        // Prepare customer details
        $customerDetails = [
            'first_name' => $customer->name ?? 'Customer',
            'email' => $customer->email,
            'phone' => $customer->phone ?? '',
        ];

        // Prepare item details
        $itemDetails = [];
        foreach ($invoice->items as $item) {
            $itemDetails[] = [
                'id' => $item->id,
                'price' => ($item->unit_price_cents ?? 0) / 100,
                'quantity' => $item->qty ?? 1,
                'name' => $item->description ?? 'Item',
            ];
        }

        // Get payment method from options
        $paymentMethod = $options['payment_method'] ?? 'credit_card';

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
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])->post("{$baseUrl}/charge", $payload);

            if (! $response->successful()) {
                Log::error('Midtrans Core API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payment_method' => $paymentMethod,
                ]);
                throw new \RuntimeException('Failed to create Midtrans payment: ' . $response->body());
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
                throw new \RuntimeException('Midtrans payment failed: ' . ($responseData['status_message'] ?? 'Unknown error'));
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
                // Default ke credit_card jika payment method tidak dikenali
                $payload['payment_type'] = 'credit_card';
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

        // Verify webhook signature (optional, recommended for production)
        // $signature = $payload['signature_key'] ?? null;
        // if (!$this->verifySignature($payload, $signature)) {
        //     Log::warning('Midtrans webhook signature verification failed', ['order_id' => $orderId]);
        //     return null;
        // }

        $payment = Payment::where('provider_ref', $orderId)->first();

        if (! $payment) {
            Log::warning('Midtrans webhook payment not found', ['order_id' => $orderId]);

            return null;
        }

        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        // Handle different transaction statuses
        // settlement = payment success
        // pending = waiting payment
        // cancel/expire = failed
        if ($transactionStatus === 'settlement' && $fraudStatus === 'accept') {
            // Payment successful
            $this->paymentRepository->markAsSucceeded($payment, $payload);
            Log::info("Midtrans payment succeeded for order: {$orderId}");
        } elseif (in_array($transactionStatus, ['cancel', 'expire', 'deny'])) {
            // Payment failed
            $payment->update([
                'status' => 'failed',
                'raw_payload' => $payload,
            ]);
            Log::info("Midtrans payment failed for order: {$orderId}", [
                'status' => $transactionStatus,
            ]);
        } else {
            // Still pending
            $payment->update([
                'raw_payload' => $payload,
            ]);
            Log::info("Midtrans payment still pending for order: {$orderId}", [
                'status' => $transactionStatus,
            ]);
        }

        return $payment;
    }

    /**
     * Verify webhook signature (optional, recommended for production)
     */
    private function verifySignature(array $payload, ?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        $serverKey = config('payment.midtrans.server_key');
        $expectedSignature = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . $serverKey);

        return hash_equals($expectedSignature, $signature);
    }
}
