<?php

namespace JordanPrice\Toolbox\Console\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'toolbox:publish {tool?}';
    protected $description = 'Publish Toolbox configuration and assets';

    protected $tools = [
        'crypto' => ['config'],
        'email' => ['config', 'views'],
        'eloquent' => ['config'],
        'time' => ['config'],
        'weather' => ['config'],
    ];

    public function handle()
    {
        $tool = $this->argument('tool');

        if ($tool && !array_key_exists($tool, $this->tools)) {
            $this->error("Tool '{$tool}' not found!");
            $this->info("\nAvailable tools:");
            foreach (array_keys($this->tools) as $availableTool) {
                $this->info("- {$availableTool}");
            }
            return 1;
        }

        if ($tool) {
            $this->publishTool($tool);
        } else {
            $this->publishAll();
        }

        return 0;
    }

    protected function publishTool(string $tool)
    {
        $this->info("Publishing {$tool} assets...");
        
        foreach ($this->tools[$tool] as $asset) {
            $this->call('vendor:publish', [
                '--provider' => 'JordanPrice\Toolbox\ToolboxServiceProvider',
                '--tag' => "toolbox-{$asset}",
                '--force' => $this->option('force'),
            ]);
        }
    }

    protected function publishAll()
    {
        $this->info('Publishing all Toolbox assets...');
        
        $this->call('vendor:publish', [
            '--provider' => 'JordanPrice\Toolbox\ToolboxServiceProvider',
            '--tag' => 'toolbox-config',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--provider' => 'JordanPrice\Toolbox\ToolboxServiceProvider',
            '--tag' => 'toolbox-views',
            '--force' => $this->option('force'),
        ]);
    }
}
