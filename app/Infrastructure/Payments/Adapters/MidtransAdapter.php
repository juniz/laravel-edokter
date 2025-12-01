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
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';

        // Load customer untuk mendapatkan data customer
        $invoice->load('customer');
        $customer = $invoice->customer;

        // Generate order ID
        $orderId = 'INV-'.$invoice->number.'-'.time();

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

        // Prepare request payload untuk Midtrans Snap API
        $payload = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        // Add payment method options jika ada
        if (isset($options['payment_method'])) {
            $payload['enabled_payments'] = [$options['payment_method']];
        }

        // Call Midtrans Snap API
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.base64_encode($serverKey.':'),
            ])->post("{$baseUrl}/transactions", $payload);

            if (! $response->successful()) {
                Log::error('Midtrans API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('Failed to create Midtrans payment: '.$response->body());
            }

            $responseData = $response->json();
            $token = $responseData['token'] ?? null;
            $redirectUrl = $responseData['redirect_url'] ?? null;

            if (! $token) {
                throw new \RuntimeException('Midtrans token not found in response');
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
                    'token' => $token,
                    'redirect_url' => $redirectUrl,
                    'snap_response' => $responseData,
                ],
            ]);

            Log::info("Midtrans payment created for invoice: {$invoice->id}", [
                'order_id' => $orderId,
                'token' => $token,
            ]);

            return $payment;
        } catch (\Exception $e) {
            Log::error("Midtrans payment creation failed for invoice: {$invoice->id}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
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
        $expectedSignature = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey);

        return hash_equals($expectedSignature, $signature);
    }
}
