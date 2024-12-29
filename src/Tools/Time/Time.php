<?php

namespace JordanPrice\Toolbox\Tools\Time;

use Carbon\Carbon;
use InvalidArgumentException;

class Time
{
    protected array $usTimezones = [
        'America/New_York',
        'America/Chicago',
        'America/Denver',
        'America/Los_Angeles',
        'America/Anchorage',
        'America/Adak',
        'Pacific/Honolulu',
        'America/Detroit',
        'America/Indiana/Indianapolis'
    ];

    protected array $formatTypes = [
        'human' => 'Relative time (e.g., "2 hours ago")',
        'date' => 'Date only (e.g., "December 29, 2023")',
        'time' => 'Time only (12/24 hour based on timezone)',
        'full' => 'Full date and time',
        'day' => 'Day and date (e.g., "Friday, December 29, 2023")',
        'iso' => 'ISO 8601 format'
    ];

    /**
     * Get the current time in a specific timezone and format
     */
    public function getCurrentTime(string $timezone = 'UTC', string $format = 'full'): string
    {
        // Get current time
        $time = Carbon::now();

        // Convert timezone if specified
        try {
            $time = $time->setTimezone($timezone);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid timezone: {$timezone}");
        }

        // Format the time
        $result = $this->formatTime($time, $format);

        // Add timezone information
        return "Current time in " . $time->tzName . ": " . $result;
    }

    /**
     * Get list of available format types
     */
    public function getFormatTypes(): array
    {
        return $this->formatTypes;
    }

    /**
     * Get list of US timezones
     */
    public function getUsTimezones(): array
    {
        return $this->usTimezones;
    }

    /**
     * Check if a timezone is a US timezone
     */
    public function isUsTimezone(string $timezone): bool
    {
        return in_array($timezone, $this->usTimezones);
    }

    /**
     * Format a Carbon time object according to the specified format
     */
    protected function formatTime(Carbon $time, string $format): string
    {
        $isUsTimezone = $this->isUsTimezone($time->tzName);

        return match ($format) {
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
    }
}
