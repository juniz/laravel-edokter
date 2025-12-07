<?php

namespace App\Http\Controllers\Auth;

use App\Application\Rdash\User\SyncUserToRdashService;
use App\Domain\Customer\Contracts\CustomerRepository;
use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationMail;
use App\Mail\TestEmailMail;
use App\Models\EmailVerification;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class EmailVerificationController extends Controller
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private SyncUserToRdashService $syncUserToRdashService
    ) {}

    /**
     * Send verification code to email
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        try {
            $verification = EmailVerification::createOrUpdate($validated['email']);

            Mail::to($validated['email'])->send(
                new EmailVerificationMail($validated['name'], $verification->code)
            );

            return response()->json([
                'success' => true,
                'message' => 'Kode verifikasi telah dikirim ke email Anda',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = 'Gagal mengirim kode verifikasi. Silakan coba lagi.';

            // Provide more specific error messages
            if (str_contains($e->getMessage(), 'Connection could not be established')) {
                $errorMessage = 'Tidak dapat terhubung ke server email. Pastikan konfigurasi SMTP sudah benar atau hubungi administrator.';
            } elseif (str_contains($e->getMessage(), 'timeout') || str_contains($e->getMessage(), 'timed out')) {
                $errorMessage = 'Koneksi ke server email timeout. Email akan dikirim melalui antrian. Silakan cek email Anda dalam beberapa saat.';
            } elseif (str_contains($e->getMessage(), 'authentication')) {
                $errorMessage = 'Gagal autentikasi ke server email. Pastikan kredensial email sudah benar.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Verify email code
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'code' => ['required', 'string', 'size:6'],
            ]);

            $verification = EmailVerification::where('email', $validated['email'])
                ->where('verified', false)
                ->latest()
                ->first();

            if (! $verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode verifikasi tidak ditemukan atau sudah digunakan',
                ], 404);
            }

            if ($verification->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode verifikasi telah kedaluwarsa. Silakan minta kode baru.',
                ], 400);
            }

            if (! $verification->verify($validated['code'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode verifikasi tidak valid',
                ], 400);
            }

            // Check if user already exists (for existing users verifying email)
            $user = User::where('email', $validated['email'])->first();

            if ($user) {
                // Existing user - just verify email and login
                $user->update(['email_verified_at' => now()]);
                Auth::login($user);
            } else {
                // New registration - create user from pending registration
                $pendingRegistration = PendingRegistration::where('email', $validated['email'])->first();

                if (! $pendingRegistration) {
                    // Log for debugging - check all pending registrations
                    $allPending = PendingRegistration::select('email', 'created_at', 'expires_at')->get();
                    Log::warning('PendingRegistration not found during verification', [
                        'requested_email' => $validated['email'],
                        'code' => $validated['code'],
                        'all_pending_count' => $allPending->count(),
                        'all_pending_emails' => $allPending->pluck('email')->toArray(),
                        'all_pending_details' => $allPending->map(function ($item) {
                            return [
                                'email' => $item->email,
                                'created_at' => $item->created_at,
                                'expires_at' => $item->expires_at,
                                'is_expired' => $item->isExpired(),
                            ];
                        })->toArray(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Data registrasi tidak ditemukan. Silakan daftar ulang.',
                        'debug' => config('app.debug') ? [
                            'requested_email' => $validated['email'],
                            'pending_count' => $allPending->count(),
                        ] : null,
                    ], 404);
                }

                if ($pendingRegistration->isExpired()) {
                    $pendingRegistration->delete();

                    return response()->json([
                        'success' => false,
                        'message' => 'Data registrasi telah kedaluwarsa. Silakan daftar ulang.',
                    ], 400);
                }

                // Create user
                $user = User::create([
                    'name' => $pendingRegistration->name,
                    'email' => $pendingRegistration->email,
                    'password' => $pendingRegistration->password, // Already hashed
                    'email_verified_at' => now(),
                ]);

                // Assign role
                $userRole = Role::where('name', 'user')->first();
                if ($userRole) {
                    $user->assignRole($userRole);
                }

                // Create customer dengan data RDASH
                $customer = $this->customerRepository->create([
                    'user_id' => $user->id,
                    'name' => $pendingRegistration->name,
                    'email' => $pendingRegistration->email,
                    'phone' => $pendingRegistration->phone,
                    'organization' => $pendingRegistration->organization,
                    'street_1' => $pendingRegistration->street_1,
                    'street_2' => $pendingRegistration->street_2,
                    'city' => $pendingRegistration->city,
                    'state' => $pendingRegistration->state,
                    'country_code' => $pendingRegistration->country_code,
                    'postal_code' => $pendingRegistration->postal_code,
                    'fax' => $pendingRegistration->fax,
                    'rdash_sync_status' => 'pending',
                ]);

                // Sync ke RDASH secara synchronous
                try {
                    $syncResult = $this->syncUserToRdashService->execute($user, false);

                    if (! $syncResult['success']) {
                        Log::warning('RDASH sync failed during registration', [
                            'user_id' => $user->id,
                            'error' => $syncResult['message'],
                        ]);
                        // Tetap lanjutkan proses registrasi meskipun sync gagal
                    }
                } catch (\Exception $e) {
                    Log::error('RDASH sync exception during registration', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Tetap lanjutkan proses registrasi meskipun sync gagal
                }

                event(new Registered($user));

                // Delete pending registration
                $pendingRegistration->delete();

                // Login user
                Auth::login($user);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email berhasil diverifikasi. Akun Anda telah dibuat.',
                'redirect' => route('dashboard'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to verify email code', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memverifikasi kode. Silakan coba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Resend verification code
     */
    public function resend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        try {
            $verification = EmailVerification::createOrUpdate($validated['email']);

            Mail::to($validated['email'])->send(
                new EmailVerificationMail($validated['name'], $verification->code)
            );

            return response()->json([
                'success' => true,
                'message' => 'Kode verifikasi baru telah dikirim ke email Anda',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to resend verification email', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = 'Gagal mengirim kode verifikasi. Silakan coba lagi.';

            // Provide more specific error messages
            if (str_contains($e->getMessage(), 'Connection could not be established')) {
                $errorMessage = 'Tidak dapat terhubung ke server email. Pastikan konfigurasi SMTP sudah benar atau hubungi administrator.';
            } elseif (str_contains($e->getMessage(), 'timeout') || str_contains($e->getMessage(), 'timed out')) {
                $errorMessage = 'Koneksi ke server email timeout. Email akan dikirim melalui antrian. Silakan cek email Anda dalam beberapa saat.';
            } elseif (str_contains($e->getMessage(), 'authentication')) {
                $errorMessage = 'Gagal autentikasi ke server email. Pastikan kredensial email sudah benar.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Test send email without queue (for testing purposes)
     */
    public function testSend(Request $request): JsonResponse
    {
        $email = $request->input('email', 'juni.yudo0@gmail.com');
        $name = $request->input('name', 'Test User');
        $code = $request->input('code', '123456');

        try {
            // Use TestEmailMail which doesn't implement ShouldQueue
            // This will send email immediately without queue
            Mail::to($email)->send(new TestEmailMail($name, $code));

            return response()->json([
                'success' => true,
                'message' => 'Email test berhasil dikirim ke ' . $email,
                'data' => [
                    'email' => $email,
                    'name' => $name,
                    'code' => $code,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = 'Gagal mengirim email test: ' . $e->getMessage();

            // Provide more specific error messages
            if (str_contains($e->getMessage(), 'Connection could not be established')) {
                $errorMessage = 'Tidak dapat terhubung ke server email. Pastikan konfigurasi SMTP sudah benar.';
            } elseif (str_contains($e->getMessage(), 'timeout') || str_contains($e->getMessage(), 'timed out')) {
                $errorMessage = 'Koneksi ke server email timeout. Periksa koneksi internet atau firewall.';
            } elseif (str_contains($e->getMessage(), 'authentication')) {
                $errorMessage = 'Gagal autentikasi ke server email. Pastikan kredensial email sudah benar.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
