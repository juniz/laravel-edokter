<?php

namespace App\Http\Controllers\Auth;

use App\Application\Rdash\User\SyncUserToRdashService;
use App\Domain\Customer\Contracts\CustomerRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ValidateStep1Request;
use App\Http\Requests\Auth\ValidateStep2Request;
use App\Mail\EmailVerificationMailSync;
use App\Models\EmailVerification;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private SyncUserToRdashService $syncUserToRdashService
    ) {}

    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Check email status and return pending registration if exists
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'lowercase'],
        ]);

        $email = $validated['email'];

        // Check if user already exists
        $userExists = User::where('email', $email)->exists();
        if ($userExists) {
            return response()->json([
                'success' => false,
                'status' => 'already_registered',
                'message' => 'Email sudah terdaftar. Silakan login dengan email tersebut.',
            ]);
        }

        // Check for pending registration
        $pendingRegistration = PendingRegistration::where('email', $email)->first();

        if ($pendingRegistration) {
            // Check if expired
            if ($pendingRegistration->isExpired()) {
                $pendingRegistration->delete();
                return response()->json([
                    'success' => true,
                    'status' => 'new_registration',
                    'message' => 'Email tersedia. Silakan lanjutkan pendaftaran.',
                ]);
            }

            // Return pending registration data (without sensitive info)
            return response()->json([
                'success' => true,
                'status' => 'pending_registration',
                'message' => 'Anda memiliki pendaftaran yang belum selesai. Apakah Anda ingin melanjutkan?',
                'data' => [
                    'email' => $pendingRegistration->email,
                    'name' => $pendingRegistration->name,
                    'organization' => $pendingRegistration->organization,
                    'phone' => $pendingRegistration->phone,
                    'street_1' => $pendingRegistration->street_1,
                    'street_2' => $pendingRegistration->street_2,
                    'city' => $pendingRegistration->city,
                    'state' => $pendingRegistration->state,
                    'country_code' => $pendingRegistration->country_code,
                    'postal_code' => $pendingRegistration->postal_code,
                    'fax' => $pendingRegistration->fax,
                    'created_at' => $pendingRegistration->created_at->toIso8601String(),
                    'expires_at' => $pendingRegistration->expires_at->toIso8601String(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => 'new_registration',
            'message' => 'Email tersedia. Silakan lanjutkan pendaftaran.',
        ]);
    }

    /**
     * Resume pending registration by loading data
     */
    public function resume(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'lowercase'],
        ]);

        $email = $validated['email'];

        $pendingRegistration = PendingRegistration::where('email', $email)->first();

        if (! $pendingRegistration) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftaran tidak ditemukan.',
            ], 404);
        }

        if ($pendingRegistration->isExpired()) {
            $pendingRegistration->delete();
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftaran telah kedaluwarsa. Silakan mulai dari awal.',
            ], 400);
        }

        // Return all data except password (password tidak bisa di-restore karena sudah di-hash)
        return response()->json([
            'success' => true,
            'data' => [
                'email' => $pendingRegistration->email,
                'name' => $pendingRegistration->name,
                'organization' => $pendingRegistration->organization,
                'phone' => $pendingRegistration->phone,
                'street_1' => $pendingRegistration->street_1,
                'street_2' => $pendingRegistration->street_2,
                'city' => $pendingRegistration->city,
                'state' => $pendingRegistration->state,
                'country_code' => $pendingRegistration->country_code,
                'postal_code' => $pendingRegistration->postal_code,
                'fax' => $pendingRegistration->fax,
            ],
            'message' => 'Data pendaftaran berhasil dimuat. Silakan lengkapi password dan lanjutkan.',
        ]);
    }

    /**
     * Start new registration (delete pending if exists)
     */
    public function startNew(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'lowercase'],
        ]);

        $email = $validated['email'];

        // Delete pending registration if exists
        $pendingRegistration = PendingRegistration::where('email', $email)->first();
        if ($pendingRegistration) {
            $pendingRegistration->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Silakan mulai pendaftaran baru.',
        ]);
    }

    /**
     * Validate step 1 (Account Information)
     */
    public function validateStep1(ValidateStep1Request $request): RedirectResponse
    {
        // Validation passed, redirect back (Inertia will handle this)
        return redirect()->back();
    }

    /**
     * Validate step 2 (RDASH Customer Data)
     */
    public function validateStep2(ValidateStep2Request $request): RedirectResponse
    {
        // Validation passed, redirect back (Inertia will handle this)
        return redirect()->back();
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        Log::info('Registration form submitted', [
            'email' => $request->input('email'),
            'all_input' => $request->all(),
        ]);

        $validated = $request->validated();

        Log::info('Registration form validated', [
            'email' => $validated['email'] ?? null,
            'validated_fields' => array_keys($validated),
        ]);

        // Check if email already exists in pending registrations
        $existingPending = PendingRegistration::where('email', $validated['email'])->first();
        if ($existingPending) {
            // Delete old pending registration
            $existingPending->delete();
        }

        // Store registration data temporarily (will be used after email verification)
        try {
            $pendingRegistration = PendingRegistration::create([
                'email' => $validated['email'],
                'name' => $validated['name'],
                'password' => Hash::make($validated['password']), // Hash password before storing
                'organization' => $validated['organization'],
                'phone' => $validated['phone'],
                'street_1' => $validated['street_1'],
                'street_2' => $validated['street_2'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'],
                'country_code' => $validated['country_code'],
                'postal_code' => $validated['postal_code'],
                'fax' => $validated['fax'] ?? null,
                'expires_at' => now()->addHours(24), // Expires in 24 hours
            ]);

            Log::info('PendingRegistration created successfully', [
                'email' => $validated['email'],
                'pending_registration_id' => $pendingRegistration->id,
                'expires_at' => $pendingRegistration->expires_at,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create PendingRegistration', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withErrors(['email' => 'Gagal menyimpan data registrasi. Silakan coba lagi.']);
        }

        // Send email verification code immediately (without queue)
        try {
            $verification = EmailVerification::createOrUpdate($validated['email']);
            // Use EmailVerificationMailSync which doesn't use queue for immediate delivery
            Mail::to($validated['email'])->send(
                new EmailVerificationMailSync($validated['name'], $verification->code)
            );

            // Log successful email sending
            Log::info('Verification email sent during registration', [
                'email' => $validated['email'],
                'pending_registration_id' => $pendingRegistration->id,
                'verification_id' => $verification->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send verification email during registration', [
                'email' => $validated['email'],
                'pending_registration_id' => $pendingRegistration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Delete pending registration if email fails
            $pendingRegistration->delete();

            return redirect()->back()->withErrors(['email' => 'Gagal mengirim kode verifikasi. Silakan coba lagi.']);
        }

        // Return to register page with success message
        return redirect()->back()->with('success', 'Kode verifikasi telah dikirim ke email Anda. Silakan verifikasi email untuk menyelesaikan registrasi.');
    }
}
