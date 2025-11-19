<?php

namespace App\Http\Controllers\Api\Rdash;

use App\Application\Rdash\Domain\CheckDomainAvailabilityService;
use App\Application\Rdash\Domain\RegisterDomainService;
use App\Domain\Rdash\Domain\Contracts\RdashDomainRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function __construct(
        private RdashDomainRepository $domainRepository,
        private CheckDomainAvailabilityService $checkAvailabilityService,
        private RegisterDomainService $registerDomainService
    ) {
    }

    /**
     * Get list all domains
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'customer_id',
            'name',
            'status',
            'verification_status',
            'required_document',
            'created_range',
            'expired_range',
            'f_params.orderBy.field',
            'f_params.orderBy.type',
            'page',
            'limit',
        ]);

        $domains = $this->domainRepository->getAll($filters);

        return response()->json([
            'success' => true,
            'data' => array_map(fn ($domain) => $domain->toArray(), $domains),
        ]);
    }

    /**
     * Get domain by id
     */
    public function show(int $domainId): JsonResponse
    {
        $domain = $this->domainRepository->getById($domainId);

        if (! $domain) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $domain->toArray(),
        ]);
    }

    /**
     * Check domain availability
     */
    public function availability(Request $request): JsonResponse
    {
        $request->validate([
            'domain' => 'required|string',
            'include_premium_domains' => 'sometimes|boolean',
        ]);

        $availability = $this->checkAvailabilityService->execute(
            $request->input('domain'),
            $request->boolean('include_premium_domains', false)
        );

        return response()->json([
            'success' => true,
            'data' => $availability->toArray(),
        ]);
    }

    /**
     * Get domain whois info
     */
    public function whois(Request $request): JsonResponse
    {
        $request->validate([
            'domain' => 'required|string',
        ]);

        $whois = $this->domainRepository->getWhois($request->input('domain'));

        return response()->json([
            'success' => true,
            'data' => $whois->toArray(),
        ]);
    }

    /**
     * Register new domain
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'period' => 'required|integer|min:1',
            'customer_id' => 'required|integer',
            'nameserver' => 'sometimes|array',
            'nameserver.*' => 'string',
            'buy_whois_protection' => 'sometimes|boolean',
            'include_premium_domains' => 'sometimes|boolean',
            'registrant_contact_id' => 'sometimes|integer',
        ]);

        // Format nameservers untuk API RDASH
        if (isset($validated['nameserver'])) {
            $nameservers = [];
            foreach ($validated['nameserver'] as $index => $ns) {
                $nameservers["nameserver[{$index}]"] = $ns;
            }
            unset($validated['nameserver']);
            $validated = array_merge($validated, $nameservers);
        }

        $domain = $this->registerDomainService->execute($validated);

        return response()->json([
            'success' => true,
            'data' => $domain->toArray(),
        ], 201);
    }

    /**
     * Transfer domain
     */
    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'auth_code' => 'required|integer',
            'period' => 'required|integer|min:1',
            'customer_id' => 'required|integer',
            'nameserver' => 'sometimes|array',
            'nameserver.*' => 'string',
            'buy_whois_protection' => 'sometimes|boolean',
        ]);

        // Format nameservers untuk API RDASH
        if (isset($validated['nameserver'])) {
            $nameservers = [];
            foreach ($validated['nameserver'] as $index => $ns) {
                $nameservers["nameserver[{$index}]"] = $ns;
            }
            unset($validated['nameserver']);
            $validated = array_merge($validated, $nameservers);
        }

        $domain = $this->domainRepository->transfer($validated);

        return response()->json([
            'success' => true,
            'data' => $domain->toArray(),
        ], 201);
    }

    /**
     * Renew domain
     */
    public function renew(Request $request, int $domainId): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'required|integer|min:1|max:10',
            'current_date' => 'required|date',
            'buy_whois_protection' => 'sometimes|boolean',
        ]);

        $domain = $this->domainRepository->renew($domainId, $validated);

        return response()->json([
            'success' => true,
            'data' => $domain->toArray(),
        ]);
    }

    /**
     * Update domain nameservers
     */
    public function updateNameservers(Request $request, int $domainId): JsonResponse
    {
        $validated = $request->validate([
            'nameserver' => 'required|array|min:2',
            'nameserver.*' => 'required|string',
            'customer_id' => 'sometimes|integer',
        ]);

        $domain = $this->domainRepository->updateNameservers(
            $domainId,
            $validated['nameserver'],
            $validated['customer_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => $domain->toArray(),
        ]);
    }

    /**
     * Get domain auth code
     */
    public function authCode(int $domainId): JsonResponse
    {
        $authCode = $this->domainRepository->getAuthCode($domainId);

        return response()->json([
            'success' => true,
            'data' => [
                'auth_code' => $authCode,
            ],
        ]);
    }

    /**
     * Reset domain auth code
     */
    public function resetAuthCode(Request $request, int $domainId): JsonResponse
    {
        $validated = $request->validate([
            'auth_code' => 'required|string|min:8|regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
        ]);

        $this->domainRepository->resetAuthCode($domainId, $validated['auth_code']);

        return response()->json([
            'success' => true,
            'message' => 'Auth code updated successfully',
        ]);
    }

    /**
     * Lock domain
     */
    public function lock(Request $request, int $domainId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'sometimes|string',
        ]);

        $this->domainRepository->lock($domainId, $validated['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Domain locked successfully',
        ]);
    }

    /**
     * Unlock domain
     */
    public function unlock(int $domainId): JsonResponse
    {
        $this->domainRepository->unlock($domainId);

        return response()->json([
            'success' => true,
            'message' => 'Domain unlocked successfully',
        ]);
    }

    /**
     * Suspend domain
     */
    public function suspend(Request $request, int $domainId): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|integer|in:1,2',
            'reason' => 'required|string',
        ]);

        $this->domainRepository->suspend($domainId, $validated['type'], $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Domain suspended successfully',
        ]);
    }

    /**
     * Unsuspend domain
     */
    public function unsuspend(int $domainId): JsonResponse
    {
        $this->domainRepository->unsuspend($domainId);

        return response()->json([
            'success' => true,
            'message' => 'Domain unsuspended successfully',
        ]);
    }
}

