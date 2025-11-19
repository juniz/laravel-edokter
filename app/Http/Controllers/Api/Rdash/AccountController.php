<?php

namespace App\Http\Controllers\Api\Rdash;

use App\Application\Rdash\Account\GetAccountProfileService;
use App\Domain\Rdash\Account\Contracts\AccountRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        private AccountRepository $accountRepository,
        private GetAccountProfileService $getAccountProfileService
    ) {
    }

    /**
     * Get reseller profile
     */
    public function profile(): JsonResponse
    {
        $profile = $this->getAccountProfileService->execute();

        return response()->json([
            'success' => true,
            'data' => $profile->toArray(),
        ]);
    }

    /**
     * Get balance amount
     */
    public function balance(): JsonResponse
    {
        $balance = $this->accountRepository->getBalance();

        return response()->json([
            'success' => true,
            'data' => $balance->toArray(),
        ]);
    }

    /**
     * Get list all domain prices
     */
    public function prices(Request $request): JsonResponse
    {
        $filters = $request->only([
            'domainExtension.extension',
            'promo',
            'page',
            'limit',
        ]);

        $prices = $this->accountRepository->getPrices($filters);

        return response()->json([
            'success' => true,
            'data' => array_map(fn ($price) => $price->toArray(), $prices),
        ]);
    }

    /**
     * Get domain price details by price id
     */
    public function price(int $priceId): JsonResponse
    {
        $price = $this->accountRepository->getPriceById($priceId);

        if (! $price) {
            return response()->json([
                'success' => false,
                'message' => 'Price not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $price->toArray(),
        ]);
    }

    /**
     * Get list all transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $filters = $request->only([
            'transaction',
            'description',
            'tld',
            'amount_range',
            'date_range',
            'page',
            'limit',
        ]);

        $transactions = $this->accountRepository->getTransactions($filters);

        return response()->json([
            'success' => true,
            'data' => array_map(fn ($transaction) => $transaction->toArray(), $transactions),
        ]);
    }

    /**
     * Get transaction details by transaction id
     */
    public function transaction(int $transactionId): JsonResponse
    {
        $transaction = $this->accountRepository->getTransactionById($transactionId);

        if (! $transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction->toArray(),
        ]);
    }
}

