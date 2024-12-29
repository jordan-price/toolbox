<?php

namespace JordanPrice\Toolbox\Tools\Email;

use EchoLabs\Prism\Tool;
use Illuminate\Support\Facades\Log;
use JordanPrice\Toolbox\Tools\Email\Email;

class EmailTool extends Tool
{
    protected Email $email;

    public function __construct()
    {
        $this->email = new Email();

        $this
            ->as('email')
            ->for('Send an email')
            ->withParameter('to', 'Email address to send to')
            ->withParameter('subject', 'Subject of the email')
            ->withParameter('body', 'Body of the email')
            ->using($this);
    }

    public function __invoke(string $to, string $subject, string $body): string
    {
        try {
            Log::info('Email Tool Input:', [
                'to' => $to,
                'subject' => $subject,
                'message_length' => strlen($body)
            ]);

            $response = $this->email->send($to, $subject, $body);

            Log::info('Email Tool Output:', ['response' => $response]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Email Tool Error:', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
