<?php

namespace App\Support;

class Naira
{
    public static function format(int|float|string|null $amount): string
    {
        $formatted = number_format((float) $amount, 2, '.', ',');

        return config('storefront.symbol', '₦').preg_replace('/\.00$/', '', $formatted);
    }

    public static function normalizeInput(mixed $amount): mixed
    {
        if (! is_string($amount)) {
            return $amount;
        }

        $amount = trim($amount);

        if (preg_match('/\A\d{1,3}(?:,\d{3})+(?:\.\d{1,2})?\z/', $amount)) {
            return str_replace(',', '', $amount);
        }

        return $amount;
    }
}