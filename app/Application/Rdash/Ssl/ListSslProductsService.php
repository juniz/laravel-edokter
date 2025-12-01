<?php

namespace App\Application\Rdash\Ssl;

use App\Domain\Rdash\Ssl\Contracts\SslRepository;

class ListSslProductsService
{
    public function __construct(
        private SslRepository $sslRepository
    ) {}

    /**
     * List SSL products dengan pagination
     *
     * @param  array<string, mixed>  $filters
     * @return array{products: array<int, \App\Domain\Rdash\Ssl\ValueObjects\SslProduct>, links: array<string, mixed>, meta: array<string, mixed>}
     */
    public function execute(array $filters = []): array
    {
        return $this->sslRepository->getProductsWithPagination($filters);
    }
}
