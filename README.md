# AI Agent Toolbox


A Laravel package providing a collection of AI-powered tools for common tasks like weather lookup, cryptocurrency prices, time conversion, and more. Built with a focus on clean architecture and AI integration.

## Installation

You can install the package via composer:

```bash
composer require jordan-price/toolbox
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="toolbox-config"
```

Add the following environment variables to your `.env` file:

```env
WEATHER_API_KEY=your_weather_api_key
TOOLBOX_AI_PROVIDER=openai
TOOLBOX_AI_MODEL=gpt-4 # or your preferred model
```

## Available Tools

### Weather Tool
Get current weather conditions for any location using WeatherAPI.com:

```php
use JordanPrice\Toolbox\Tools\Weather\WeatherTool;

$weather = new WeatherTool();
$response = $weather('London'); // "Current weather in London, UK: Temperature: 15.0°C (59.0°F)..."
```

### Crypto Tool
Get cryptocurrency price information from CoinGecko. You can use the CryptoClient directly or through the AI tool interface:

```php
// Direct CryptoClient usage
use JordanPrice\Toolbox\Tools\Crypto\CryptoClient;

$client = new CryptoClient();

// Get price information
$result = $client->getPrice('bitcoin', 'usd');
// "Current Bitcoin price: $42,123.45 USD
//  24h change: +2.34%
//  Last updated: 2024-12-29 13:14:05"

// Get supported currencies
$currencies = $client->getSupportedCurrencies();
// ['usd', 'eur', 'gbp', 'jpy']

// Get popular coins
$coins = $client->getPopularCoins();
// ['bitcoin', 'ethereum', 'solana', 'cardano', 'dogecoin']

// AI tool usage
use JordanPrice\Toolbox\Tools\Crypto\CryptoTool;

$tool = new CryptoTool();
$response = $tool('bitcoin', 'usd');
// Same formatted output as above
```

### Time Tool
Get current time in different timezones and formats. You can use the Time class directly or through the AI tool interface:

```php
// Direct Time usage
use JordanPrice\Toolbox\Tools\Time\Time;

$time = new Time();

// Get current time in New York
$result = $time->getCurrentTime('America/New_York', 'full');
// "Current time in America/New_York: 2:14 PM on December 29, 2023"

// Get available format types
$formats = $time->getFormatTypes();
// ['human' => 'Relative time...', 'date' => 'Date only...', ...]

// Check if timezone is US-based
$isUs = $time->isUsTimezone('America/Chicago'); // true

// AI tool usage
use JordanPrice\Toolbox\Tools\Time\TimeTool;

$tool = new TimeTool();
$response = $tool('America/New_York', 'full');
// Same formatted output as above
```

### Email Tool
Send emails with customizable templates. You can use the Email class directly or through the AI tool interface:

```php
// Direct Email usage
use JordanPrice\Toolbox\Tools\Email\Email;

$email = new Email();

// Send an email
$result = $email->send('user@example.com', 'Hello', 'This is a test email');

// Configure custom mailable class
$email->setDefaultMailableClass(\App\Mail\CustomEmail::class);

// Configure custom view
$email->setDefaultView('emails.custom');

// Validate an email address
$isValid = $email->validateEmail('user@example.com'); // true

// AI tool usage
use JordanPrice\Toolbox\Tools\Email\EmailTool;

$tool = new EmailTool();
$response = $tool('user@example.com', 'Hello', 'This is a test email');
```

You can customize the default email template and mailable class in your config:

```php
// config/toolbox.php
return [
    'email' => [
        'mailable' => \App\Mail\CustomEmail::class,
        'view' => 'emails.custom'
    ]
];
```

### Calculator Tool
Perform mathematical calculations. You can use the Calculator directly or through the AI tool interface:

```php
// Direct calculator usage
use JordanPrice\Toolbox\Tools\Calculator\Calculator;

$calc = new Calculator();
$result = $calc->evaluate('2 + 2 * 4'); // Returns: 10
$formatted = $calc->formatResult('2 + 2 * 4', $result); // "Result of 2 + 2 * 4 = 10"

// AI tool usage
use JordanPrice\Toolbox\Tools\Calculator\CalculatorTool;

$tool = new CalculatorTool();
$response = $tool('2 + 2 * 4'); // "Result of 2 + 2 * 4 = 10"
```

### Eloquent Tool
Execute safe database queries. You can use the Eloquent class directly or through the AI tool interface:

```php
// Direct Eloquent usage
use JordanPrice\Toolbox\Tools\Eloquent\Eloquent;

$eloquent = new Eloquent();

// Get all active users
$result = $eloquent->query('User', 'where', [['active', '=', true]]);
$formatted = $eloquent->formatResult($result);

// Get available models and operations
$models = $eloquent->getAllowedModels(); // ['User', 'Post', ...]
$operations = $eloquent->getAllowedOperations(); // ['select', 'where', ...]

// AI tool usage
use JordanPrice\Toolbox\Tools\Eloquent\EloquentTool;

$tool = new EloquentTool();
$response = $tool('User', 'where', [['active', '=', true]]); 
// Returns formatted JSON of active users
```

## AI Integration with Prism

Use the tools with Prism for AI-powered interactions:

```php
use JordanPrice\Toolbox\Tools\Weather\WeatherTool;
use JordanPrice\Toolbox\Tools\Crypto\CryptoTool;
use EchoLabs\Prism\Prism;

$response = Prism::text()
    ->using(config('toolbox.ai.provider'), config('toolbox.ai.model'))
    ->withSystemPrompt("You are a helpful assistant...")
    ->withPrompt("What's the weather in London and the price of Bitcoin?")
    ->withTools([
        new WeatherTool(),
        new CryptoTool()
    ])
    ->generate();
```

### Livewire Component

The package includes a ready-to-use Livewire component:

```blade
<livewire:weather-assistant />
```

## Architecture

The package follows a clean, modular architecture:

```
src/
  Tools/
    Weather/
      WeatherClient.php  # API client
      WeatherTool.php    # Prism tool
    Crypto/
      CryptoClient.php   # API client
      CryptoTool.php     # Prism tool
    Time/
      Time.php           # Time conversion class
      TimeTool.php       # AI tool interface
    Email/
      Email.php          # Email sending class
      EmailTool.php      # AI tool interface
      Mail/              # Email templates
      views/             # Email views
    Calculator/
      Calculator.php     # Math calculation class
      CalculatorTool.php # AI tool interface
    Eloquent/
      Eloquent.php       # Database query class
      EloquentTool.php   # AI tool interface
```

Each tool is self-contained in its own directory with all necessary components.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@bestie.ai instead of using the issue tracker.

## Credits

- [Jordan Price](https://github.com/jordanprice)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
