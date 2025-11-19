<?php

namespace App\Http\Controllers\Domain\Catalog;

use App\Http\Controllers\Controller;
use App\Domain\Catalog\Contracts\ProductRepository;
use App\Domain\Catalog\Contracts\PlanRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(
        private ProductRepository $productRepository,
        private PlanRepository $planRepository
    ) {}

    public function index(): Response
    {
        $products = $this->productRepository->findAllActive();

        return Inertia::render('catalog/Index', [
            'products' => $products,
        ]);
    }

    public function show(string $slug): Response
    {
        $product = $this->productRepository->findBySlug($slug);
        
        if (!$product) {
            abort(404);
        }

        $plans = $this->planRepository->findByProduct($product->id);

        return Inertia::render('catalog/Show', [
            'product' => $product,
            'plans' => $plans,
        ]);
    }
}
