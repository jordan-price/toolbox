# Ai Agent Toolbox

A Laravel package providing various tools including calculator, weather, email, time, and cryptocurrency tools.

## Installation

You can install the package via composer:

```bash
composer require bestie/toolbox
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="toolbox-config"
```

Add the following environment variables to your `.env` file:

```env
WEATHER_API_KEY=your_weather_api_key
CRYPTO_API_KEY=your_crypto_api_key
TOOLBOX_AI_MODEL=gpt-4 # or your preferred model
```

## Usage

Add the Livewire component to your blade view:

```blade
<livewire:toolbox-chat />
```

Or use the included blade component:

```blade
@include('toolbox::chat')
```

## Available Tools

1. Calculator Tool: Perform mathematical calculations
2. Weather Tool: Get weather information for any location
3. Email Tool: Send emails with various types of information
4. Time Tool: Get current time in different timezones
5. Eloquent Tool: Execute Eloquent queries
6. Crypto Tool: Get cryptocurrency prices and information

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email jordan@bestie.ai instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
