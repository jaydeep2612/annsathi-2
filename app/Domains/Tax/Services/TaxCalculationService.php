<?php

declare(strict_types=1);

namespace App\Domains\Tax\Services;

use App\Domains\Tax\Models\TaxGroup;
use App\Domains\Tax\Models\TaxRate;

class TaxCalculationService
{
    /**
     * Calculate tax for a given amount and tax group.
     *
     * @param float $amount The base amount (or total amount for inclusive tax)
     * @param int|TaxGroup $taxGroup The tax group model or ID
     * @param bool $isInclusive Whether the amount is tax inclusive
     * @return array Returns an array with:
     *               - 'subtotal' (float): The base price before taxes
     *               - 'tax_total' (float): The total tax amount calculated
     *               - 'grand_total' (float): The total price after taxes
     *               - 'details' (array): Array of tax rates with details
     */
    public function calculate(float $amount, int|TaxGroup|null $taxGroup, bool $isInclusive = false): array
    {
        // Null checks
        if ($taxGroup === null) {
            return [
                'subtotal' => $amount,
                'tax_total' => 0.0,
                'grand_total' => $amount,
                'details' => [],
            ];
        }

        // 1. Resolve tax group
        if (is_int($taxGroup)) {
            $taxGroup = TaxGroup::with('rates')->find($taxGroup);
        }

        if (! $taxGroup || ! $taxGroup->rates || $taxGroup->rates->isEmpty()) {
            return [
                'subtotal' => $amount,
                'tax_total' => 0.0,
                'grand_total' => $amount,
                'details' => [],
            ];
        }

        // 2. Sum the active tax rates in the group
        $totalRatePercent = 0.0;
        $activeRates = [];
        foreach ($taxGroup->rates as $rate) {
            if ($rate->is_active) {
                $totalRatePercent += (float) $rate->rate;
                $activeRates[] = $rate;
            }
        }

        if ($totalRatePercent === 0.0) {
            return [
                'subtotal' => $amount,
                'tax_total' => 0.0,
                'grand_total' => $amount,
                'details' => [],
            ];
        }

        $subtotal = 0.0;
        $taxTotal = 0.0;
        $grandTotal = 0.0;
        $details = [];

        // 3. Perform calculations
        if ($isInclusive) {
            // Formula: Base = Total / (1 + Rate)
            $subtotal = $amount / (1 + ($totalRatePercent / 100));
            $taxTotal = $amount - $subtotal;
            $grandTotal = $amount;

            // Distribute calculated tax proportionally among active rates
            foreach ($activeRates as $rate) {
                $proportion = (float) $rate->rate / $totalRatePercent;
                $rateTaxAmount = $taxTotal * $proportion;
                $details[] = [
                    'tax_rate_id' => $rate->id,
                    'name' => $rate->name,
                    'rate' => (float) $rate->rate,
                    'amount' => round($rateTaxAmount, 2),
                ];
            }
        } else {
            // Formula: Tax = Base * Rate
            $subtotal = $amount;
            $taxTotal = 0.0;

            foreach ($activeRates as $rate) {
                $rateTaxAmount = $amount * ((float) $rate->rate / 100);
                $taxTotal += $rateTaxAmount;
                $details[] = [
                    'tax_rate_id' => $rate->id,
                    'name' => $rate->name,
                    'rate' => (float) $rate->rate,
                    'amount' => round($rateTaxAmount, 2),
                ];
            }

            $grandTotal = $subtotal + $taxTotal;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'grand_total' => round($grandTotal, 2),
            'details' => $details,
        ];
    }
}
