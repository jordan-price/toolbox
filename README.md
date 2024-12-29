# AI Agent Toolbox

A collection of AI-powered tools designed to work with Laravel applications.

## Features

- **Weather**: Get real-time weather data and forecasts
- **Time**: Handle time operations and timezone conversions
- **Crypto**: Fetch cryptocurrency price information
- **Email**: Send emails with ease
- **Eloquent**: Execute database queries using natural language

## Installation

You can install the package via composer:

```bash
composer require jordan-price/toolbox
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="toolbox-config"
```

## Configuration

After installation, you can configure the package in your `.env` file:

```env
# Weather Tool (optional)
WEATHER_API_KEY=your_api_key
```

## Usage

### Weather

Get real-time weather data:

```php
use JordanPrice\Toolbox\Tools\Weather\WeatherTool;
use EchoLabs\Prism\Prism;

$prism = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withPrompt('What is the weather in London?')
    ->withTools([WeatherTool::class])
    ->toolChoice(ToolChoice::Any);

$response = $prism->generate();
```

### Time

Handle time operations:

```php
use JordanPrice\Toolbox\Tools\Time\TimeTool;
use EchoLabs\Prism\Prism;

$prism = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withPrompt('What time is it in New York?')
    ->withTools([TimeTool::class])
    ->toolChoice(ToolChoice::Any);

$response = $prism->generate();
```

### Crypto

Get cryptocurrency prices:

```php
use JordanPrice\Toolbox\Tools\Crypto\CryptoTool;
use EchoLabs\Prism\Prism;

$prism = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withPrompt('What is the current price of Bitcoin?')
    ->withTools([CryptoTool::class])
    ->toolChoice(ToolChoice::Any);

$response = $prism->generate();
```

### Email

Send emails:

```php
use JordanPrice\Toolbox\Tools\Email\EmailTool;
use EchoLabs\Prism\Prism;

$prism = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withPrompt('Send a welcome email to user@example.com')
    ->withTools([EmailTool::class])
    ->toolChoice(ToolChoice::Any);

$response = $prism->generate();
```

### Eloquent

Execute database queries:

```php
use JordanPrice\Toolbox\Tools\Eloquent\EloquentTool;
use EchoLabs\Prism\Prism;

$prism = Prism::text()
    ->using(Provider::Anthropic, 'claude-3-5-sonnet-latest')
    ->withPrompt('Find all active users')
    ->withTools([EloquentTool::class])
    ->toolChoice(ToolChoice::Any);

$response = $prism->generate();
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@bestie.ai instead of using the issue tracker.

## Credits

- [Jordan Price](https://github.com/jordanprice)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
