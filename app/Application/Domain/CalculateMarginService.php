<?php

namespace App\Application\Domain;

use App\Models\Domain\Shared\Setting;

class CalculateMarginService
{
    /**
     * Calculate price with margin for domain
     *
     * @param  int  $basePrice  Base price in IDR (not cents)
     * @return int Price with margin in IDR (not cents)
     */
    public function calculateDomainPrice(int $basePrice): int
    {
        $marginSettings = Setting::where('key', 'profit_margin')->first();

        if (! $marginSettings || ! isset($marginSettings->value['domain_margin_type'])) {
            return $basePrice;
        }

        $marginType = $marginSettings->value['domain_margin_type'];
        $marginValue = (float) ($marginSettings->value['domain_margin_value'] ?? 0);

        return $this->applyMargin($basePrice, $marginType, $marginValue);
    }

    /**
     * Calculate price with margin for SSH
     *
     * @param  int  $basePrice  Base price in IDR (not cents)
     * @return int Price with margin in IDR (not cents)
     */
    public function calculateSshPrice(int $basePrice): int
    {
        $marginSettings = Setting::where('key', 'profit_margin')->first();

        if (! $marginSettings || ! isset($marginSettings->value['ssh_margin_type'])) {
            return $basePrice;
        }

        $marginType = $marginSettings->value['ssh_margin_type'];
        $marginValue = (float) ($marginSettings->value['ssh_margin_value'] ?? 0);

        return $this->applyMargin($basePrice, $marginType, $marginValue);
    }

    /**
     * Apply margin to base price
     *
     * @param  int  $basePrice  Base price in IDR
     * @param  string  $marginType  'percentage' or 'fixed'
     * @param  float  $marginValue  Margin value
     * @return int Price with margin in IDR
     */
    private function applyMargin(int $basePrice, string $marginType, float $marginValue): int
    {
        if ($marginValue <= 0) {
            return $basePrice;
        }

        if ($marginType === 'percentage') {
            return (int) round($basePrice * (1 + $marginValue / 100));
        }

        // Fixed margin
        return (int) round($basePrice + $marginValue);
    }
}
