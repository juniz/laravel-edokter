<?php

namespace App\Domain\Rdash\Account\ValueObjects;

readonly class DomainPrice
{
    public function __construct(
        public int $id,
        public array $domainExtension,
        public string $currency,
        public array $registration,
        public array $renewal,
        public string $transfer,
        public string $redemption,
        public string $proxy,
        public ?array $promoRegistration = null
    ) {}

    public static function fromArray(array $data): self
    {
        $domainExtension = $data['domain_extension'] ?? [];
        $extension = $domainExtension['extension'] ?? '';

        // Get registration price for 1 year as default price
        $registration = $data['registration'] ?? [];
        $price = isset($registration['1']) ? (int) $registration['1'] : 0;

        // Get renewal price for 1 year as default renew price
        $renewal = $data['renewal'] ?? [];
        $renewPrice = isset($renewal['1']) ? (int) $renewal['1'] : 0;

        // Check if has promo
        $promoRegistration = $data['promo_registration'] ?? null;
        $hasPromo = ! empty($promoRegistration) && ! empty($promoRegistration['registration']);

        return new self(
            id: $data['id'] ?? 0,
            domainExtension: $domainExtension,
            currency: $data['currency'] ?? 'IDR',
            registration: $registration,
            renewal: $renewal,
            transfer: (string) ($data['transfer'] ?? '0.00'),
            redemption: (string) ($data['redemption'] ?? '0.00'),
            proxy: (string) ($data['proxy'] ?? '0.00'),
            promoRegistration: $promoRegistration
        );
    }

    public function toArray(): array
    {
        $extension = $this->domainExtension['extension'] ?? '';
        $registration = $this->registration;
        $renewal = $this->renewal;
        $price = isset($registration['1']) ? (int) $registration['1'] : 0;
        $renewPrice = isset($renewal['1']) ? (int) $renewal['1'] : 0;
        $hasPromo = ! empty($this->promoRegistration) && ! empty($this->promoRegistration['registration']);

        return [
            'id' => $this->id,
            'extension' => $extension,
            'domain_extension' => $this->domainExtension,
            'currency' => $this->currency,
            'price' => $price,
            'renew_price' => $renewPrice,
            'transfer_price' => (float) $this->transfer,
            'redemption_price' => (float) $this->redemption,
            'proxy_price' => (float) $this->proxy,
            'registration' => $registration,
            'renewal' => $renewal,
            'transfer' => $this->transfer,
            'redemption' => $this->redemption,
            'proxy' => $this->proxy,
            'promo' => $hasPromo,
            'promo_registration' => $this->promoRegistration,
        ];
    }
}
