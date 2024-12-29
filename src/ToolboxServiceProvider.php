<?php

namespace Bestie\Toolbox;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Bestie\Toolbox\Livewire\Chat;

class ToolboxServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/toolbox.php', 'toolbox'
        );
    }

    public function boot()
    {
        // Register Livewire components
        Livewire::component('toolbox-chat', Chat::class);

        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/toolbox.php' => config_path('toolbox.php'),
        ], 'toolbox-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/toolbox'),
        ], 'toolbox-views');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'toolbox');
    }
}
