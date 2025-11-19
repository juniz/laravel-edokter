<?php

namespace App\Application\Rdash\Account;

use App\Domain\Rdash\Account\Contracts\AccountRepository;
use App\Domain\Rdash\Account\ValueObjects\AccountProfile;

class GetAccountProfileService
{
    public function __construct(
        private AccountRepository $accountRepository
    ) {
    }

    public function execute(): AccountProfile
    {
        return $this->accountRepository->getProfile();
    }
}

