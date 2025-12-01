<?php

namespace App\Http\Controllers\Domain;

use App\Domain\Rdash\Account\Contracts\AccountRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DomainPriceController extends Controller
{
    public function __construct(
        private AccountRepository $accountRepository
    ) {}

    /**
     * Tampilkan daftar harga domain dari RDASH
     */
    public function index(Request $request): Response
    {
        $filters = $request->only([
            'extension',
            'promo',
        ]);

        $rdashFilters = [];

        if (! empty($filters['extension'])) {
            $rdashFilters['domainExtension.extension'] = $filters['extension'];
        }

        if (isset($filters['promo']) && $filters['promo'] !== '') {
            $rdashFilters['promo'] = $filters['promo'];
        }

        $prices = $this->accountRepository->getPrices($rdashFilters);

        return Inertia::render('admin/domain-prices/Index', [
            'prices' => array_map(static fn ($price) => $price->toArray(), $prices),
            'filters' => $filters,
        ]);
    }
}
