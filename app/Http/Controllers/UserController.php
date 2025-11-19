<?php

namespace App\Http\Controllers;

use App\Application\Rdash\User\BulkSyncUsersToRdashService;
use App\Application\Rdash\User\GetRdashCustomerForUserService;
use App\Application\Rdash\User\SyncUserToRdashService;
use App\Application\Rdash\User\UpdateRdashCustomerService;
use App\Events\UserCreated;
use App\Events\UserUpdated;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'customer'])
            ->withCount('customer');

        // Search by name or email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by RDASH sync status
        if ($request->has('rdash_status') && $request->rdash_status) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('rdash_sync_status', $request->rdash_status);
            });
        }

        $users = $query->latest()->paginate($request->get('per_page', 10));

        // Add RDASH sync status to each user
        $users->getCollection()->transform(function ($user) {
            $user->rdash_sync_status = $user->customer?->rdash_sync_status ?? null;
            $user->rdash_customer_id = $user->customer?->rdash_customer_id ?? null;
            $user->rdash_synced_at = $user->customer?->rdash_synced_at ?? null;
            return $user;
        });

        // Get stats
        $stats = [
            'total' => User::count(),
            'synced' => User::whereHas('customer', function ($q) {
                $q->where('rdash_sync_status', 'synced');
            })->count(),
            'pending' => User::whereHas('customer', function ($q) {
                $q->where('rdash_sync_status', 'pending');
            })->count(),
            'failed' => User::whereHas('customer', function ($q) {
                $q->where('rdash_sync_status', 'failed');
            })->count(),
        ];

        // Get all roles for filter
        $roles = \Spatie\Permission\Models\Role::all()->pluck('name');

        return Inertia::render('users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'rdash_status', 'per_page']),
            'stats' => $stats,
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $roles = Role::all();

        return Inertia::render('users/Form', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => ['required', Rule::exists('roles', 'name')],
            'create_rdash_customer' => ['sometimes', 'boolean'],
            // RDASH customer fields (optional, akan menggunakan default jika tidak ada)
            'rdash_customer' => ['sometimes', 'array'],
            'rdash_customer.organization' => ['sometimes', 'string', 'max:255'],
            'rdash_customer.street_1' => ['sometimes', 'string', 'max:255'],
            'rdash_customer.street_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'rdash_customer.city' => ['sometimes', 'string', 'max:255'],
            'rdash_customer.state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'rdash_customer.country_code' => ['sometimes', 'string', 'size:2'],
            'rdash_customer.postal_code' => ['sometimes', 'string', 'max:20'],
            'rdash_customer.phone' => ['sometimes', 'string', 'min:9', 'max:20'],
            'rdash_customer.fax' => ['sometimes', 'nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['roles']);

        // Jika create_rdash_customer dicentang, buat customer dengan data RDASH
        if ($request->boolean('create_rdash_customer')) {
            $rdashCustomerData = $validated['rdash_customer'] ?? [];
            
            // Create customer dengan data RDASH
            $customer = \App\Models\Domain\Customer\Customer::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $rdashCustomerData['phone'] ?? null,
                'organization' => $rdashCustomerData['organization'] ?? $user->name,
                'street_1' => $rdashCustomerData['street_1'] ?? 'Not Provided',
                'street_2' => $rdashCustomerData['street_2'] ?? null,
                'city' => $rdashCustomerData['city'] ?? 'Jakarta',
                'state' => $rdashCustomerData['state'] ?? null,
                'country_code' => $rdashCustomerData['country_code'] ?? 'ID',
                'postal_code' => $rdashCustomerData['postal_code'] ?? '00000',
                'fax' => $rdashCustomerData['fax'] ?? null,
                'rdash_sync_status' => 'pending',
            ]);

            // Sync langsung ke RDASH (synchronous)
            $syncService = app(SyncUserToRdashService::class);
            $syncService->execute($user, false);
        }

        // Dispatch UserCreated event
        event(new UserCreated($user));

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    public function show(User $user, GetRdashCustomerForUserService $getRdashCustomerService)
    {
        $user->load(['roles', 'customer']);
        
        $rdashCustomer = $getRdashCustomerService->execute($user);

        return Inertia::render('users/Show', [
            'user' => $user,
            'rdashCustomer' => $rdashCustomer,
        ]);
    }

    public function edit(User $user)
    {
        $roles = Role::all();

        return Inertia::render('users/Form', [
            'user'         => $user->only(['id', 'name', 'email']),
            'roles'        => $roles,
            'currentRoles' => $user->roles->pluck('name')->toArray(), // multiple roles
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => ['required', Rule::exists('roles', 'name')],
            'sync_rdash' => ['sometimes', 'boolean'],
        ]);

        $user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password']
                ? Hash::make($validated['password'])
                : $user->password,
        ]);

        $user->syncRoles($validated['roles']);

        // Dispatch UserUpdated event
        event(new UserUpdated($user));

        // If sync_rdash is checked, sync to RDASH (synchronous)
        if ($request->boolean('sync_rdash')) {
            $syncService = app(SyncUserToRdashService::class);
            $syncService->execute($user, true);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make('ResetPasswordNya'),
        ]);

        return redirect()->back()->with('success', 'Password berhasil direset ke default.');
    }

    /**
     * Manual sync user ke RDASH (synchronous)
     */
    public function syncRdash(
        User $user,
        SyncUserToRdashService $syncService,
        Request $request
    ) {
        // Sync langsung (synchronous)
        $result = $syncService->execute($user, true);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Bulk sync multiple users ke RDASH (synchronous)
     */
    public function bulkSyncRdash(Request $request, BulkSyncUsersToRdashService $bulkSyncService)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        $result = $bulkSyncService->execute($validated['user_ids']);

        return redirect()->back()->with(
            'success',
            'Bulk sync selesai: ' . $result['success_count'] . ' berhasil, ' . $result['failed_count'] . ' gagal.'
        );
    }

    /**
     * Get RDASH customer details untuk user
     */
    public function getRdashCustomer(
        User $user,
        GetRdashCustomerForUserService $getRdashCustomerService
    ) {
        $rdashCustomer = $getRdashCustomerService->execute($user);

        if (! $rdashCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak memiliki customer di RDASH',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $rdashCustomer,
        ]);
    }

    /**
     * Update RDASH customer untuk user
     */
    public function updateRdashCustomer(
        User $user,
        UpdateRdashCustomerService $updateService,
        Request $request
    ) {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'organization' => ['sometimes', 'string', 'max:255'],
            'street_1' => ['sometimes', 'string', 'max:255'],
            'street_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'country_code' => ['sometimes', 'string', 'size:2'],
            'postal_code' => ['sometimes', 'string', 'max:20'],
            'voice' => ['sometimes', 'string', 'min:9', 'max:20'],
            'phone' => ['sometimes', 'string', 'min:9', 'max:20'], // Alias untuk voice
            'fax' => ['sometimes', 'nullable', 'string', 'max:20'],
        ]);

        $result = $updateService->execute($user, $validated);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
