<?php

namespace App\Http\Controllers\Api\Payment;

use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Http\Controllers\Controller;
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
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();

            Log::info('Midtrans webhook received', [
                'payload' => $payload,
            ]);

            // Handle webhook via adapter
            $payment = $this->paymentAdapter->handleWebhook($payload);

            if (! $payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }
}
