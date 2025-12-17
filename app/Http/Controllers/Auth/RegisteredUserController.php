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
use Illuminate\Http\RedirectResponse;
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
