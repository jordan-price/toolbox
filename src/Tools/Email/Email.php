<?php

namespace JordanPrice\Toolbox\Tools\Email;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use JordanPrice\Toolbox\Tools\Email\Mail\GeneralEmail;
use Illuminate\Mail\Mailable;
use InvalidArgumentException;

class Email
{
    protected string $defaultMailableClass;
    protected string $defaultView;

    public function __construct()
    {
        $this->defaultMailableClass = config('toolbox.email.mailable', GeneralEmail::class);
        $this->defaultView = config('toolbox.email.view', 'email::general');

        // Register the email views directory
        View::addNamespace('email', __DIR__ . '/views');
    }

    /**
     * Send an email
     */
    public function send(string $to, string $subject, string $body): string
    {
        // Validate email
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$to}");
        }

        // Create mailable instance
        $mailableClass = $this->defaultMailableClass;
        /** @var Mailable $mailable */
        $mailable = new $mailableClass(
            messageBody: $body,
            emailSubject: $subject
        );

        // Send the email
        Mail::to($to)->send($mailable);

        return "Email sent successfully to {$to}";
    }

    /**
     * Get the default mailable class
     */
    public function getDefaultMailableClass(): string
    {
        return $this->defaultMailableClass;
    }

    /**
     * Set the default mailable class
     */
    public function setDefaultMailableClass(string $class): void
    {
        if (!class_exists($class) || !is_subclass_of($class, Mailable::class)) {
            throw new InvalidArgumentException("Invalid mailable class: {$class}");
        }
        $this->defaultMailableClass = $class;
    }

    /**
     * Get the default view
     */
    public function getDefaultView(): string
    {
        return $this->defaultView;
    }

    /**
     * Set the default view
     */
    public function setDefaultView(string $view): void
    {
        if (!View::exists($view)) {
            throw new InvalidArgumentException("View does not exist: {$view}");
        }
        $this->defaultView = $view;
    }

    /**
     * Validate an email address
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
