<?php

namespace JordanPrice\Toolbox\Tools\Weather;

use EchoLabs\Prism\Tool;
use Illuminate\Support\Facades\Log;

class WeatherTool extends Tool
{
    protected WeatherClient $client;

    public function __construct()
    {
        $this->client = new WeatherClient();

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

            $result = $this->client->formatWeatherResponse(
                $this->client->getCurrentWeather(['q' => $location])
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
