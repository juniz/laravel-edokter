<?php

namespace App\Domain\Rdash\Account\Contracts;

use App\Domain\Rdash\Account\ValueObjects\AccountBalance;
use App\Domain\Rdash\Account\ValueObjects\AccountProfile;
use App\Domain\Rdash\Account\ValueObjects\DomainPrice;
use App\Domain\Rdash\Account\ValueObjects\Transaction;

interface AccountRepository
{
    /**
     * Get reseller profile
     */
    public function getProfile(): AccountProfile;

    /**
     * Get balance amount
     */
    public function getBalance(): AccountBalance;

    /**
     * Get list all domain prices
     *
     * @param  array<string, mixed>  $filters
     * @return array{data: array<int, DomainPrice>, links: array<string, mixed>, meta: array<string, mixed>}
     */
    public function getPrices(array $filters = []): array;

    /**
     * Get domain price details by price id
     */
    public function getPriceById(int $priceId): ?DomainPrice;

    /**
     * Get list all transactions
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, Transaction>
     */
    public function getTransactions(array $filters = []): array;

    /**
     * Get transaction details by transaction id
     */
    public function getTransactionById(int $transactionId): ?Transaction;
}
