<?php

namespace App\Http\Controllers\Domain\Billing;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\PaymentRepository;
use App\Models\Domain\Billing\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function show(string $id): Response
    {
        $payment = Payment::with(['invoice.customer', 'invoice.items'])
            ->findOrFail($id);

        // Extract payment information dari raw_payload
        $rawPayload = $payment->raw_payload ?? [];
        $redirectUrl = $rawPayload['redirect_url'] ?? null;
        $coreApiResponse = $rawPayload['core_api_response'] ?? [];

        // Extract VA number untuk bank transfer
        $vaNumber = null;
        if (isset($coreApiResponse['va_numbers']) && is_array($coreApiResponse['va_numbers']) && count($coreApiResponse['va_numbers']) > 0) {
            $vaNumber = $coreApiResponse['va_numbers'][0]['va_number'] ?? null;
        } elseif (isset($coreApiResponse['permata_va_number'])) {
            $vaNumber = $coreApiResponse['permata_va_number'];
        } elseif (isset($coreApiResponse['bill_key']) && isset($coreApiResponse['biller_code'])) {
            // Mandiri VA
            $vaNumber = $coreApiResponse['bill_key'];
        }

        // Extract payment code untuk convenience store
        $paymentCode = null;
        if (isset($coreApiResponse['payment_code'])) {
            $paymentCode = $coreApiResponse['payment_code'];
        }

        // Extract QR code URL untuk e-wallet
        $qrCodeUrl = null;
        if (isset($coreApiResponse['actions']) && is_array($coreApiResponse['actions'])) {
            foreach ($coreApiResponse['actions'] as $action) {
                if (isset($action['name']) && $action['name'] === 'generate-qr-code' && isset($action['url'])) {
                    $qrCodeUrl = $action['url'];
                    break;
                }
            }
        }

        // Extract expiry time
        $expiryTime = null;
        if (isset($coreApiResponse['expiry_time'])) {
            $expiryTime = $coreApiResponse['expiry_time'];
        }

        return Inertia::render('payments/Show', [
            'payment' => [
                'id' => $payment->id,
                'status' => $payment->status,
                'amount_cents' => $payment->amount_cents,
                'provider' => $payment->provider,
                'provider_ref' => $payment->provider_ref,
                'created_at' => $payment->created_at,
                'invoice' => [
                    'id' => $payment->invoice->id,
                    'number' => $payment->invoice->number,
                    'total_cents' => $payment->invoice->total_cents,
                    'currency' => $payment->invoice->currency,
                ],
            ],
            'redirect_url' => $redirectUrl,
            'va_number' => $vaNumber,
            'payment_code' => $paymentCode,
            'qr_code_url' => $qrCodeUrl,
            'expiry_time' => $expiryTime,
            'payment_method' => $rawPayload['payment_method'] ?? ($payment->provider === 'manual' ? 'manual' : null),
        ]);
    }

    public function approve(Request $request, string $id, PaymentRepository $paymentRepository): RedirectResponse
    {
        $payment = Payment::with(['invoice'])->findOrFail($id);

        if ($payment->provider !== 'manual' || $payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Pembayaran ini tidak dapat di-approve.');
        }

        $payload = array_merge($payment->raw_payload ?? [], [
            'manual_approval' => true,
            'approved_by' => $request->user()?->id,
            'approved_at' => now()->toISOString(),
        ]);

        $paymentRepository->markAsSucceeded($payment, $payload);

        return redirect()->back()->with('success', 'Pembayaran manual berhasil di-approve.');
    }

    public function reject(Request $request, string $id): RedirectResponse
    {
        $payment = Payment::with(['invoice'])->findOrFail($id);

        if ($payment->provider !== 'manual' || $payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Pembayaran ini tidak dapat ditolak.');
        }

        $payment->update([
            'status' => 'failed',
            'raw_payload' => array_merge($payment->raw_payload ?? [], [
                'manual_approval' => true,
                'rejected_by' => $request->user()?->id,
                'rejected_at' => now()->toISOString(),
            ]),
        ]);

        return redirect()->back()->with('success', 'Pembayaran manual ditolak.');
    }
}
