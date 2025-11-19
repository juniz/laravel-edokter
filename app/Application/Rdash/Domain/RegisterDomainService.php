<?php

namespace App\Application\Rdash\Domain;

use App\Domain\Rdash\Domain\Contracts\RdashDomainRepository;
use App\Domain\Rdash\Domain\ValueObjects\RdashDomain;

class RegisterDomainService
{
    public function __construct(
        private RdashDomainRepository $domainRepository
    ) {
    }

    /**
     * Register a new domain
     *
     * @param array<string, mixed> $data
     */
    public function execute(array $data): RdashDomain
    {
        // Validasi data bisa ditambahkan di sini atau di FormRequest
        return $this->domainRepository->register($data);
    }
}

