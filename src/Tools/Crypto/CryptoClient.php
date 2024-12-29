<?php

namespace JordanPrice\Toolbox\Tools\Crypto;

use Illuminate\Support\Facades\Http;

class CryptoClient
{
    protected string $baseUrl = 'https://api.coingecko.com/api/v3';
    protected array $supportedCurrencies = ['usd', 'eur', 'gbp', 'jpy'];
    protected array $popularCoins = ['bitcoin', 'ethereum', 'solana', 'cardano', 'dogecoin'];
    protected array $currencySymbols = [
        'usd' => '$',
        'eur' => '€',
        'gbp' => '£',
        'jpy' => '¥'
    ];

    /**
     * Get current price for a cryptocurrency in a specific currency
     */
    public function getPrice(string $coin, string $currency = 'usd'): string
    {
        $coin = $this->validateCoin($coin);
        $currency = $this->validateCurrency($currency);

        $data = $this->getCurrentPrice([
            'ids' => $coin,
            'vs_currencies' => $currency,
            'include_24hr_change' => 'true',
            'include_last_updated_at' => 'true'
        ]);

        return $this->formatPriceResponse($data);
    }

    /**
     * Get raw price data from the API
     */
    protected function getCurrentPrice(array $params): array
    {
        $response = Http::get("{$this->baseUrl}/simple/price", array_merge([
            'include_24hr_change' => true,
            'include_last_updated_at' => true,
        ], $params));

        if (!$response->successful()) {
            throw new \Exception("Crypto API error: " . ($response->json('error') ?? $response->status()));
        }

        $data = $response->json();
        $coin = $params['ids'] ?? null;

        if ($coin && empty($data[$coin])) {
            throw new \Exception("Cryptocurrency '{$coin}' not found.");
        }

        return $data;
    }

    /**
     * Format the price API response into a human-readable string
     */
    protected function formatPriceResponse(array $data): string
    {
        $coin = array_key_first($data);
        $info = $data[$coin];
        $currency = array_key_first($info);
        
        $price = $this->formatPrice($info[$currency], $currency);
        $change = $info["{$currency}_24h_change"] ?? null;
        $lastUpdated = $info['last_updated_at'] ?? null;

        $response = "Current " . ucfirst($coin) . " price: " . $price;

        if ($change !== null) {
            $response .= sprintf("\n24h change: %+.2f%%", $change);
        }

        if ($lastUpdated !== null) {
            $response .= "\nLast updated: " . date('Y-m-d H:i:s', $lastUpdated);
        }

        return $response;
    }

    /**
     * Format a price with appropriate currency symbol and decimal places
     */
    protected function formatPrice(float $price, string $currency): string
    {
        $symbol = $this->currencySymbols[strtolower($currency)] ?? '';

        // Format with appropriate decimal places
        if ($price < 1) {
            $formatted = number_format($price, 4);
        } else {
            $formatted = number_format($price, 2);
        }

        return "{$symbol}{$formatted} " . strtoupper($currency);
    }

    /**
     * Validate and normalize a currency
     */
    protected function validateCurrency(string $currency): string
    {
        $currency = strtolower($currency);
        if (!in_array($currency, $this->supportedCurrencies)) {
            throw new \InvalidArgumentException(
                "Currency '{$currency}' not supported. Supported currencies: " .
                implode(', ', $this->supportedCurrencies)
            );
        }
        return $currency;
    }

    /**
     * Validate and normalize a coin name
     */
    protected function validateCoin(string $coin): string
    {
        return strtolower($coin);
    }

    /**
     * Get list of supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    /**
     * Get list of popular coins
     */
    public function getPopularCoins(): array
    {
        return $this->popularCoins;
    }
}
