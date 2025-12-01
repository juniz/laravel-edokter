<?php

namespace App\Http\Controllers\Domain\Ssl;

use App\Application\Rdash\Ssl\ListSslProductsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SslController extends Controller
{
    public function __construct(
        private ListSslProductsService $listSslProductsService
    ) {}

    /**
     * Display a listing of SSL products
     */
    public function index(Request $request): Response
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

        // Set default values
        $filters['page'] = $filters['page'] ?? 1;
        $filters['limit'] = $filters['limit'] ?? 15;
        $filters['status'] = $filters['status'] ?? 1; // Default hanya active products

        $result = $this->listSslProductsService->execute($filters);

        return Inertia::render('ssl/Index', [
            'products' => array_map(fn ($product) => $product->toArray(), $result['products']),
            'links' => $result['links'],
            'meta' => $result['meta'],
            'filters' => $filters,
        ]);
    }
}
