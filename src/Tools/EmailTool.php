<?php

namespace App\Tools;

use App\Mail\GeneralEmail;
use EchoLabs\Prism\Tool;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class EmailTool extends Tool
{
    public function __construct()
    {
        $this
            ->as('email')
            ->for('Send emails to specified recipients')
            ->withStringParameter('to', 'Email address of the recipient')
            ->withStringParameter('subject', 'Subject of the email')
            ->withStringParameter('message', 'Content of the email')
            ->using($this);
    }

    public function __invoke(string $to, string $subject, string $message): string
    {
        try {
            // Validate email
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Invalid email address: {$to}");
            }

            Log::info('Email Tool Input:', [
                'to' => $to,
                'subject' => $subject,
                'message_length' => strlen($message)
            ]);

            // Send the email
            Mail::to($to)->send(new GeneralEmail(
                messageBody: $message,
                emailSubject: $subject
            ));

            $response = "Email sent successfully to {$to}";
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
