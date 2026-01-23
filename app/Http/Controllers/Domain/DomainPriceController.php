<?php

namespace App\Http\Controllers\Domain;

use App\Domain\Rdash\Account\Contracts\AccountRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DomainPriceController extends Controller
{
    public function __construct(
        private AccountRepository $accountRepository,
        private \App\Application\Domain\CalculateMarginService $calculateMarginService
    ) {}

    /**
     * Tampilkan daftar harga domain dari RDASH
     */
    public function index(Request $request): Response
    {
        $filters = $request->only([
            'extension',
            'promo',
            'page',
            'limit',
        ]);

        $rdashFilters = [];

        if (! empty($filters['extension'])) {
            $rdashFilters['domainExtension[extension]'] = $filters['extension'];
        }

        if (isset($filters['promo']) && $filters['promo'] !== '') {
            $rdashFilters['promo'] = filter_var($filters['promo'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($filters['page'])) {
            $rdashFilters['page'] = (int) $filters['page'];
        }

        if (isset($filters['limit'])) {
            $rdashFilters['limit'] = (int) $filters['limit'];
        }

        $result = $this->accountRepository->getPrices($rdashFilters);

        // Apply margins to list
        $prices = array_map(function ($price) {
            $priceData = $price->toArray();
            return $this->applyMarginsToPrice($priceData);
        }, $result['data']);

        // Use same view for both customer and admin
        $viewPath = 'domain-prices/Index';

        return Inertia::render($viewPath, [
            'prices' => [
                'data' => $prices,
                'links' => $result['links'] ?? [],
                'meta' => $result['meta'] ?? [],
            ],
            'filters' => $filters,
        ]);
    }

    /**
     * Get domain price details by price id
     */
    public function show(string $priceId): \Illuminate\Http\JsonResponse
    {
        try {
            $price = $this->accountRepository->getPriceById((int) $priceId);

            if (! $price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Price not found',
                ], 404);
            }
            
            $priceData = $this->applyMarginsToPrice($price->toArray());

            return response()->json([
                'success' => true,
                'data' => $priceData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch price: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get domain price by extension
     */
    public function getByExtension(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $extension = $request->input('extension');

            if (empty($extension)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Extension is required',
                ], 400);
            }

            $rdashFilters = [
                'domainExtension[extension]' => $extension,
                'limit' => 1,
            ];

            $result = $this->accountRepository->getPrices($rdashFilters);

            if (empty($result['data'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Price not found for extension: ' . $extension,
                ], 404);
            }

            $priceData = $this->applyMarginsToPrice($result['data'][0]->toArray());

            return response()->json([
                'success' => true,
                'data' => $priceData,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get domain price by extension', [
                'extension' => $request->input('extension'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch price: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get domain price by extension for guest checkout (public access)
     */
    public function getByExtensionGuest(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $extension = $request->input('extension');

            if (empty($extension)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Extension is required',
                ], 400);
            }

            $rdashFilters = [
                'domainExtension[extension]' => $extension,
                'limit' => 1,
            ];

            $result = $this->accountRepository->getPrices($rdashFilters);

            if (empty($result['data'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Price not found for extension: ' . $extension,
                ], 404);
            }

            $priceData = $this->applyMarginsToPrice($result['data'][0]->toArray());

            return response()->json([
                'success' => true,
                'data' => $priceData,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get domain price by extension (guest)', [
                'extension' => $request->input('extension'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch price: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply margins to price data arrays (registration, transfer, renewal)
     */
    private function applyMarginsToPrice(array $priceData): array
    {
        // Apply to registration prices
        if (isset($priceData['registration']) && is_array($priceData['registration'])) {
            foreach ($priceData['registration'] as $period => $price) {
                if (is_numeric($price)) {
                    $priceData['registration'][$period] = $this->calculateMarginService->calculateDomainPrice((int)$price);
                }
            }
        }

        // Apply to transfer prices
        if (isset($priceData['transfer']) && is_array($priceData['transfer'])) {
            foreach ($priceData['transfer'] as $period => $price) {
                 if (is_numeric($price)) {
                    $priceData['transfer'][$period] = $this->calculateMarginService->calculateDomainPrice((int)$price);
                }
            }
        }

        // Apply to renewal prices
        if (isset($priceData['renewal']) && is_array($priceData['renewal'])) {
            foreach ($priceData['renewal'] as $period => $price) {
                 if (is_numeric($price)) {
                    $priceData['renewal'][$period] = $this->calculateMarginService->calculateDomainPrice((int)$price);
                }
            }
        }

        // Apply to promo registration prices
        if (isset($priceData['promo_registration']['registration']) && is_array($priceData['promo_registration']['registration'])) {
            foreach ($priceData['promo_registration']['registration'] as $period => $price) {
                 if (is_numeric($price)) {
                    $priceData['promo_registration']['registration'][$period] = $this->calculateMarginService->calculateDomainPrice((int)$price);
                }
            }
        }

        return $priceData;
    }
}
