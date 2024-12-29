<?php

namespace JordanPrice\Toolbox;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use JordanPrice\Toolbox\Livewire\Chat;

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
        // Load views with explicit namespace
        $this->loadViewsFrom(__DIR__.'/../resources/views/toolbox', 'toolbox');

        // Register Livewire components
        Livewire::component('toolbox-chat', Chat::class);

        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/toolbox.php' => config_path('toolbox.php'),
        ], 'toolbox-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views/toolbox' => resource_path('views/vendor/toolbox'),
        ], 'toolbox-views');
    }
}
