<?php

namespace JordanPrice\Toolbox\Livewire;

use Livewire\Component;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\Enums\Provider;
use JordanPrice\Toolbox\Tools\CalculatorTool;
use JordanPrice\Toolbox\Tools\WeatherTool;
use JordanPrice\Toolbox\Tools\EmailTool;
use JordanPrice\Toolbox\Tools\TimeTool;
use JordanPrice\Toolbox\Tools\EloquentTool;
use JordanPrice\Toolbox\Tools\CryptoTool;
use Illuminate\Support\Facades\Log;

class Chat extends Component
{
    public $messages = [];
    public $newMessage = '';
    public $isProcessing = false;
    public $debugInfo = [
        'text' => '',
        'has_steps' => false,
        'steps_count' => 0,
        'raw_response' => null,
        'last_tool' => '',
        'compound_task_completed' => false,
        'tool_results' => [],
        'processing_time' => 0,
        'email_sent' => false,
        'last_tool_result' => null,
        'total_tokens' => 0
    ];
    public $availableTools = [];
    protected $tools = [];

    // ... [Previous methods remain the same, just update namespaces]

    public function render()
    {
        return view('toolbox::chat');
    }
}
