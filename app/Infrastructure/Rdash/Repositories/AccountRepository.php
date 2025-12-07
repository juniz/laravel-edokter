<?php

namespace App\Infrastructure\Rdash\Repositories;

use App\Domain\Rdash\Account\Contracts\AccountRepository as AccountRepositoryContract;
use App\Domain\Rdash\Account\ValueObjects\AccountBalance;
use App\Domain\Rdash\Account\ValueObjects\AccountProfile;
use App\Domain\Rdash\Account\ValueObjects\DomainPrice;
use App\Domain\Rdash\Account\ValueObjects\Transaction;
use App\Infrastructure\Rdash\HttpClient;

class AccountRepository implements AccountRepositoryContract
{
    public function __construct(
        private HttpClient $client
    ) {}

    public function getProfile(): AccountProfile
    {
        $data = $this->client->get('/account/profile');

        return AccountProfile::fromArray($data);
    }

    public function getBalance(): AccountBalance
    {
        $data = $this->client->get('/account/balance');

        return AccountBalance::fromArray($data);
    }

    public function getPrices(array $filters = []): array
    {
        $data = $this->client->get('/account/prices', $filters);

        // Handle error response (404 or other errors)
        if (isset($data['success']) && $data['success'] === false) {
            return [
                'data' => [],
                'links' => [],
                'meta' => [],
            ];
        }

        $prices = $data['data'] ?? [];
        $links = $data['links'] ?? [];
        $meta = $data['meta'] ?? [];

        return [
            'data' => array_map(
                fn (array $price) => DomainPrice::fromArray($price),
                $prices
            ),
            'links' => $links,
            'meta' => $meta,
        ];
    }

    public function getPriceById(int $priceId): ?DomainPrice
    {
        $response = $this->client->get("/account/prices/{$priceId}");

        // Handle 404 response (resource not found)
        if (isset($response['success']) && $response['success'] === false) {
            return null;
        }

        // Handle response wrapper (success, data, message)
        $data = $response['data'] ?? $response;

        if (empty($data) || ! isset($data['id'])) {
            return null;
        }

        return DomainPrice::fromArray($data);
    }

    public function getTransactions(array $filters = []): array
    {
        $data = $this->client->get('/account/transactions', $filters);
        $transactions = $data['data'] ?? [];

        return array_map(
            fn (array $transaction) => Transaction::fromArray($transaction),
            $transactions
        );
    }

    public function getTransactionById(int $transactionId): ?Transaction
    {
        $data = $this->client->get("/account/transactions/{$transactionId}");

        return empty($data) ? null : Transaction::fromArray($data);
    }
}
