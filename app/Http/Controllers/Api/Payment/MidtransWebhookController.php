<?php

namespace App\Http\Controllers\Api\Payment;

use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Http\Controllers\Controller;
use App\Models\Domain\Billing\MidtransWebhookLog;
use App\Models\Domain\Billing\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private PaymentAdapterInterface $paymentAdapter
    ) {}

    /**
     * Handle Midtrans webhook notification
     *
     * Midtrans akan mengirim POST request ke endpoint ini ketika ada perubahan status transaksi.
     * Endpoint ini harus dapat diakses publik (tanpa authentication) karena dipanggil oleh Midtrans server.
     *
     * @see https://docs.midtrans.com/docs/core-api-status-code
     */
    public function handle(Request $request): JsonResponse
    {
        $webhookLog = null;
        $response = null;

        try {
            $payload = $request->all();

            // Validasi payload minimal
            if (empty($payload) || ! isset($payload['order_id'])) {
                // Log invalid payload
                MidtransWebhookLog::create([
                    'order_id' => $payload['order_id'] ?? null,
                    'processing_status' => 'failed',
                    'error_message' => 'Invalid payload: order_id missing',
                    'payload' => $payload,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                Log::warning('Midtrans webhook received invalid payload', [
                    'payload_keys' => array_keys($payload),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payload',
                ], 400);
            }

            // Cari payment berdasarkan order_id
            $payment = Payment::where('provider_ref', $payload['order_id'])->first();

            // Simpan webhook log sebelum processing
            $webhookLog = MidtransWebhookLog::create([
                'order_id' => $payload['order_id'],
                'payment_id' => $payment?->id,
                'transaction_status' => $payload['transaction_status'] ?? null,
                'fraud_status' => $payload['fraud_status'] ?? null,
                'payment_type' => $payload['payment_type'] ?? null,
                'status_code' => $payload['status_code'] ?? null,
                'status_message' => $payload['status_message'] ?? null,
                'processing_status' => 'pending',
                'payload' => $payload,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Log webhook received
            Log::info('Midtrans webhook received', [
                'order_id' => $payload['order_id'],
                'transaction_status' => $payload['transaction_status'] ?? null,
                'payment_type' => $payload['payment_type'] ?? null,
                'webhook_log_id' => $webhookLog->id,
            ]);

            // Handle webhook via adapter
            $payment = $this->paymentAdapter->handleWebhook($payload);

            if (! $payment) {
                // Payment tidak ditemukan
                $webhookLog->update([
                    'processing_status' => 'failed',
                    'error_message' => 'Payment not found',
                ]);

                Log::warning('Midtrans webhook: Payment not found', [
                    'order_id' => $payload['order_id'],
                    'webhook_log_id' => $webhookLog->id,
                ]);

                $response = response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                ], 200); // Return 200 agar Midtrans tidak retry
            } else {
                // Update payment_id jika sebelumnya null
                if (! $webhookLog->payment_id) {
                    $webhookLog->update(['payment_id' => $payment->id]);
                }

                // Success
                $webhookLog->update([
                    'processing_status' => 'success',
                    'payment_id' => $payment->id,
                ]);

                $response = response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully',
                    'order_id' => $payment->provider_ref,
                ], 200);
            }

            // Simpan response ke log
            $webhookLog->update([
                'response' => [
                    'status_code' => $response->getStatusCode(),
                    'content' => json_decode($response->getContent(), true),
                ],
            ]);

            return $response;
        } catch (\Exception $e) {
            // Update webhook log dengan error
            if ($webhookLog) {
                $webhookLog->update([
                    'processing_status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            } else {
                // Buat log baru jika belum ada
                MidtransWebhookLog::create([
                    'order_id' => $request->input('order_id'),
                    'processing_status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'payload' => $request->all(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            Log::error('Midtrans webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
                'webhook_log_id' => $webhookLog?->id,
            ]);

            // Return 500 agar Midtrans akan retry webhook
            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
