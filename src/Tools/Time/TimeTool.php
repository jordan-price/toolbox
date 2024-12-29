<?php

namespace JordanPrice\Toolbox\Tools\Time;

use EchoLabs\Prism\Tool;
use Illuminate\Support\Facades\Log;
use JordanPrice\Toolbox\Tools\Time\Time;

class TimeTool extends Tool
{
    protected Time $time;

    public function __construct()
    {
        $this->time = new Time();

        $this
            ->as('time')
            ->for('Get current time in specified timezone and format')
            ->withParameter('timezone', 'Target timezone (e.g., America/Chicago, UTC). Default is local timezone.')
            ->withParameter('format', sprintf(
                'Output format (%s). Default is full.',
                implode(', ', array_keys($this->time->getFormatTypes()))
            ))
            ->using($this);
    }

    public function __invoke(string $timezone = 'UTC', string $format = 'full'): string
    {
        try {
            Log::info('Time Tool Input:', [
                'timezone' => $timezone,
                'format' => $format
            ]);

            $response = $this->time->getCurrentTime($timezone, $format);

            Log::info('Time Tool Output:', ['response' => $response]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Time Tool Error:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
