<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $customer = $user->customer;

        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'customer' => $customer ? [
                'organization' => $customer->organization,
                'phone' => $customer->phone,
                'street_1' => $customer->street_1,
                'street_2' => $customer->street_2,
                'city' => $customer->city,
                'state' => $customer->state,
                'country_code' => $customer->country_code,
                'postal_code' => $customer->postal_code,
                'fax' => $customer->fax,
            ] : null,
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Update user data
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Update or create customer data
        $customer = $user->customer;

        if ($customer) {
            $customer->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? $customer->phone,
                'organization' => $validated['organization'] ?? $customer->organization,
                'street_1' => $validated['street_1'] ?? $customer->street_1,
                'street_2' => $validated['street_2'] ?? $customer->street_2,
                'city' => $validated['city'] ?? $customer->city,
                'state' => $validated['state'] ?? $customer->state,
                'country_code' => $validated['country_code'] ?? $customer->country_code,
                'postal_code' => $validated['postal_code'] ?? $customer->postal_code,
                'fax' => $validated['fax'] ?? $customer->fax,
            ]);
        } else {
            // Create customer if doesn't exist
            // Use validated input with sensible defaults, consistent with update logic
            $user->customer()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'organization' => $validated['organization'] ?? $validated['name'],
                'street_1' => $validated['street_1'] ?? null,
                'street_2' => $validated['street_2'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'country_code' => $validated['country_code'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'fax' => $validated['fax'] ?? null,
                'rdash_sync_status' => 'pending',
            ]);
        }

        return to_route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
