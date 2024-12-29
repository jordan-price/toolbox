<?php

namespace JordanPrice\Toolbox\Tools\Weather;

use Illuminate\Support\Facades\Http;

class WeatherClient
{
    protected string $baseUrl = 'https://api.weatherapi.com/v1';
    protected ?string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('toolbox.weather.api_key');
    }

    /**
     * Get the current weather for a location
     */
    public function getCurrentWeather(array $params): array
    {
        $response = Http::get("{$this->baseUrl}/current.json", array_merge([
            'key' => $this->apiKey,
        ], $params));

        if (!$response->successful()) {
            throw new \Exception("Weather API error: " . ($response->json('error.message') ?? $response->status()));
        }

        return $response->json();
    }

    /**
     * Format the weather API response into a human-readable string
     */
    public function formatWeatherResponse(array $data): string
    {
        $current = $data['current'];
        $location = $data['location'];

        return sprintf(
            "Current weather in %s, %s:\n" .
                "Temperature: %.1f°C (%.1f°F)\n" .
                "Condition: %s\n" .
                "Humidity: %d%%\n" .
                "Wind: %.1f km/h %s\n" .
                "Last updated: %s",
            $location['name'],
            $location['country'],
            $current['temp_c'],
            $current['temp_f'],
            $current['condition']['text'],
            $current['humidity'],
            $current['wind_kph'],
            $current['wind_dir'],
            $current['last_updated']
        );
    }
}
