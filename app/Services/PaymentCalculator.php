<?php

namespace App\Services;

use App\Models\Setting;

class PaymentCalculator
{
    // ── Configurações (lidas do banco via Setting) ─────────────────────────

    public function pixDiscountPct(): float
    {
        return (float) Setting::get('payment_pix_discount', 0);
    }

    public function installmentsMax(): int
    {
        return max(1, (int) Setting::get('payment_installments_max', 12));
    }

    public function installmentsFree(): int
    {
        return max(1, (int) Setting::get('payment_installments_free', 1));
    }

    public function installmentMinValue(): float
    {
        return max(0.01, (float) Setting::get('payment_installment_min_value', 10.00));
    }

    public function interestRateMonthly(): float
    {
        return (float) Setting::get('payment_interest_rate', 0);
    }

    public function cardDisplayMode(): string
    {
        return Setting::get('payment_card_display', 'both');
    }

    // ── Cálculos PIX ──────────────────────────────────────────────────────

    /**
     * Retorna o preço com desconto PIX, ou null se desconto = 0.
     */
    public function pixPrice(float $total): ?float
    {
        $discount = $this->pixDiscountPct();

        if ($discount <= 0) {
            return null;
        }

        return round($total * (1 - $discount / 100), 2);
    }

    /**
     * Retorna o valor economizado pagando no PIX.
     */
    public function pixSavings(float $total): float
    {
        $pixPrice = $this->pixPrice($total);

        if ($pixPrice === null) {
            return 0.0;
        }

        return round($total - $pixPrice, 2);
    }

    // ── Cálculos de parcelamento ──────────────────────────────────────────

    /**
     * Retorna array de opções de parcelamento para o total informado.
     * Cada item: ['value' => int, 'label' => string, 'installment_value' => float, 'interest_free' => bool]
     */
    public function installmentOptions(float $total): array
    {
        $max       = $this->installmentsMax();
        $free      = $this->installmentsFree();
        $minValue  = $this->installmentMinValue();
        $rate      = $this->interestRateMonthly() / 100;

        $options = [];

        for ($i = 1; $i <= $max; $i++) {
            if ($i <= $free || $rate <= 0) {
                // Sem juros
                $installmentValue = round($total / $i, 2);
                $interestFree     = true;
            } else {
                // Tabela Price: P = PV × r(1+r)^n / ((1+r)^n − 1)
                $installmentValue = round($total * ($rate * (1 + $rate) ** $i) / ((1 + $rate) ** $i - 1), 2);
                $interestFree     = false;
            }

            if ($installmentValue < $minValue) {
                break;
            }

            $label = $i === 1
                ? '1x de R$ ' . number_format($installmentValue, 2, ',', '.') . ' (à vista)'
                : "{$i}x de R$ " . number_format($installmentValue, 2, ',', '.') . ($interestFree ? ' sem juros' : ' com juros');

            $options[] = [
                'value'             => $i,
                'label'             => $label,
                'installment_value' => $installmentValue,
                'interest_free'     => $interestFree,
            ];
        }

        return $options;
    }

    /**
     * Retorna o label do melhor parcelamento sem juros (ex: "3x de R$ 33,33 sem juros"),
     * ou null quando não há parcelamento livre (free < 2) ou o total é muito baixo.
     */
    public function bestFreeInstallmentLabel(float $total): ?string
    {
        $free     = $this->installmentsFree();
        $minValue = $this->installmentMinValue();

        if ($free < 2) {
            return null;
        }

        // Tenta do maior ao menor número de parcelas sem juros
        for ($i = $free; $i >= 2; $i--) {
            $installmentValue = round($total / $i, 2);

            if ($installmentValue >= $minValue) {
                return "{$i}x de R$ " . number_format($installmentValue, 2, ',', '.') . ' sem juros';
            }
        }

        return null;
    }
}
