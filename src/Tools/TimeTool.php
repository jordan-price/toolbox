<?php

namespace App\Tools;

use Carbon\Carbon;
use EchoLabs\Prism\Tool;
use Illuminate\Support\Facades\Log;

class TimeTool extends Tool
{
    protected array $usTimezones = [
        'America/New_York',
        'America/Chicago',
        'America/Denver',
        'America/Los_Angeles',
        'America/Anchorage',
        'America/Adak',
        'America/Phoenix',
        'Pacific/Honolulu',
        'America/Detroit',
        'America/Indiana/Indianapolis'
    ];

    public function __construct()
    {
        $this
            ->as('time')
            ->for('Get current time, convert between timezones, or format time')
            ->withStringParameter('timezone', 'Target timezone (e.g., America/Chicago, UTC). Default is local timezone.')
            ->withStringParameter('format', 'Output format (e.g., "Y-m-d h:i A", "human"). Default is full.')
            ->using($this);
    }

    public function __invoke(string $timezone = 'UTC', string $format = 'full'): string
    {
        try {
            Log::info('Time Tool Input:', [
                'timezone' => $timezone,
                'format' => $format
            ]);

            // Get current time
            $time = Carbon::now();

            // Convert timezone if specified
            try {
                $time = $time->setTimezone($timezone);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid timezone: {$timezone}");
            }

            // Check if it's a US timezone
            $isUsTimezone = in_array($timezone, $this->usTimezones);

            // Format the time
            $result = match($format) {
                'human' => $time->diffForHumans(),
                'date' => $time->format('F j, Y'),
                'time' => $isUsTimezone ? $time->format('g:i A') : $time->format('H:i'),
                'full' => $isUsTimezone 
                    ? $time->format('g:i A') . ' on ' . $time->format('F j, Y')
                    : $time->format('H:i') . ' on ' . $time->format('F j, Y'),
                'day' => $time->format('l, F j, Y'),
                'iso' => $time->toIso8601String(),
                default => $isUsTimezone ? $time->format('g:i A') : $time->format('H:i')
            };

            // Add timezone information
            $response = "Current time in " . $time->tzName . ": " . $result;

            Log::info('Time Tool Output:', ['response' => $response]);
            
            return $response;

        } catch (\Exception $e) {
            Log::error('Time Tool Error:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
