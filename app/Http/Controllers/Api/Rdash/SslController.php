<?php

namespace App\Http\Controllers\Api\Rdash;

use App\Application\Rdash\Ssl\ListSslProductsService;
use App\Domain\Rdash\Ssl\Contracts\SslRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SslController extends Controller
{
    public function __construct(
        private SslRepository $sslRepository,
        private ListSslProductsService $listSslProductsService
    ) {}

    /**
     * Get list all SSL products
     */
    public function products(Request $request): JsonResponse
    {
        $filters = $request->only([
            'name',
            'provider',
            'brand',
            'ssl_type',
            'is_wildcard',
            'status',
            'page',
            'limit',
        ]);

        $result = $this->listSslProductsService->execute($filters);

        return response()->json([
            'success' => true,
            'data' => array_map(fn ($product) => $product->toArray(), $result['products']),
            'links' => $result['links'],
            'meta' => $result['meta'],
        ]);
    }

    /**
     * Get SSL products with prices
     */
    public function productsWithPrices(Request $request): JsonResponse
    {
        $filters = $request->only([
            'name',
            'provider',
            'brand',
            'ssl_type',
            'is_wildcard',
            'status',
            'page',
            'limit',
        ]);

        $products = $this->sslRepository->getProductsWithPrices($filters);

        return response()->json([
            'success' => true,
            'data' => array_map(fn ($product) => $product->toArray(), $products),
        ]);
    }

    /**
     * Get SSL orders
     */
    public function orders(Request $request): JsonResponse
    {
        $filters = $request->only([
            'customer_id',
            'domain',
            'status',
            'page',
            'limit',
        ]);

        $orders = $this->sslRepository->getOrders($filters);

        return response()->json([
            'success' => true,
            'data' => array_map(fn ($order) => $order->toArray(), $orders),
        ]);
    }

    /**
     * Get SSL order by id
     */
    public function order(int $sslOrderId): JsonResponse
    {
        $order = $this->sslRepository->getOrderById($sslOrderId);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'SSL order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order->toArray(),
        ]);
    }
}
