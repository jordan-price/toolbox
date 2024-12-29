<?php

namespace JordanPrice\Toolbox\Tools\Crypto;

use EchoLabs\Prism\Tool;
use Illuminate\Support\Facades\Log;

class CryptoTool extends Tool
{
    protected CryptoClient $client;

    public function __construct()
    {
        $this->client = new CryptoClient();

        $this
            ->as('crypto')
            ->for('Get cryptocurrency price information')
            ->withParameter('coin', sprintf(
                'The cryptocurrency to get price for (e.g., %s)',
                implode(', ', $this->client->getPopularCoins())
            ))
            ->withParameter('currency', sprintf(
                'The currency to show price in (e.g., %s)',
                implode(', ', $this->client->getSupportedCurrencies())
            ))
            ->using($this);
    }

    public function getPopularCoins(): array
    {
        return $this->client->getPopularCoins();
    }

    public function getSupportedCurrencies(): array
    {
        return $this->client->getSupportedCurrencies();
    }

    public function __invoke(string $coin, ?string $currency = 'usd'): string
    {
        try {
            Log::info('Crypto Tool Input:', [
                'coin' => $coin,
                'currency' => $currency
            ]);

            $response = $this->client->getPrice($coin, $currency);

            Log::info('Crypto Tool Output:', ['response' => $response]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Crypto Tool Error:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
