<?php

namespace App\Tools;

use EchoLabs\Prism\Tool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoTool extends Tool
{
    protected string $baseUrl = 'https://api.coingecko.com/api/v3';
    protected array $supportedCurrencies = ['usd', 'eur', 'gbp', 'jpy'];
    protected array $popularCoins = ['bitcoin', 'ethereum', 'solana', 'cardano', 'dogecoin'];

    public function __construct()
    {
        $this
            ->as('crypto')
            ->for('Get current cryptocurrency prices and market data')
            ->withStringParameter('coin', 'The cryptocurrency to get price for (e.g., bitcoin, ethereum)')
            ->withStringParameter('currency', 'The currency to show price in (e.g., usd, eur)')
            ->using($this);
    }

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

    protected function validateCoin(string $coin): string
    {
        $coin = strtolower($coin);
        // If it's not in our popular list, we'll still try to fetch it
        // The CoinGecko API will return an error if it's invalid
        return $coin;
    }

    protected function formatPrice(float $price, string $currency): string
    {
        $symbols = [
            'usd' => '$',
            'eur' => '€',
            'gbp' => '£',
            'jpy' => '¥'
        ];

        $symbol = $symbols[$currency] ?? '';
        
        // Format with appropriate decimal places
        if ($price < 1) {
            $formatted = number_format($price, 4);
        } elseif ($price < 100) {
            $formatted = number_format($price, 2);
        } else {
            $formatted = number_format($price, 2);
        }

        return "{$symbol}{$formatted}";
    }

    public function __invoke(string $coin, ?string $currency = 'usd'): string
    {
        try {
            Log::info('Crypto Tool Input:', [
                'coin' => $coin,
                'currency' => $currency
            ]);

            $coin = $this->validateCoin($coin);
            $currency = $this->validateCurrency($currency);

            $response = Http::get("{$this->baseUrl}/simple/price", [
                'ids' => $coin,
                'vs_currencies' => $currency,
                'include_24hr_change' => 'true',
                'include_last_updated_at' => 'true'
            ]);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch crypto price: " . $response->body());
            }

            $data = $response->json();
            
            if (empty($data[$coin])) {
                throw new \Exception("Cryptocurrency '{$coin}' not found.");
            }

            $priceData = $data[$coin];
            $price = $priceData["{$currency}"];
            $change24h = $priceData["{$currency}_24h_change"] ?? null;
            $lastUpdated = $priceData['last_updated_at'] ?? null;

            $formattedPrice = $this->formatPrice($price, $currency);
            $formattedChange = $change24h ? sprintf("%+.2f%%", $change24h) : "N/A";
            $lastUpdatedStr = $lastUpdated ? date('Y-m-d H:i:s', $lastUpdated) : "N/A";

            $response = "Current {$coin} price: {$formattedPrice}\n";
            $response .= "24h change: {$formattedChange}\n";
            $response .= "Last updated: {$lastUpdatedStr}";

            Log::info('Crypto Tool Output:', ['response' => $response]);
            
            return $response;

        } catch (\Exception $e) {
            Log::error('Crypto Tool Error:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
