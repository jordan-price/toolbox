<?php

namespace JordanPrice\Toolbox\Tools;

use EchoLabs\Prism\Tool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class WeatherTool extends Tool
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.weatherapi.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openweathermap.key');

        if (empty($this->apiKey)) {
            throw new InvalidArgumentException('OpenWeatherMap API key is not configured');
        }

        $this
            ->as('weather')
            ->for('Get current weather information for a location')
            ->withParameter('location', 'Location to get the weather for. Can be a city name, zipcode, IP address, or lat/lng coordinates. Example: "London"')
            ->using($this);
    }

    public function __invoke(string $location): string
    {
        try {
            Log::info('Weather Tool Input:', ['location' => $location]);

            $response = Http::get("{$this->baseUrl}/current.json", [
                'key' => $this->apiKey,
                'q' => $location,
            ]);

            if (!$response->successful()) {
                throw new InvalidArgumentException(
                    "Weather API error: " . ($response->json('error.message') ?? $response->status())
                );
            }

            $data = $response->json();
            $current = $data['current'];
            $location = $data['location'];

            // Format a human-readable response
            $result = sprintf(
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

            Log::info('Weather Tool Output:', ['response' => $result]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Weather Tool Error:', [
                'location' => $location,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
