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

    protected function getAvailableTools(): array
    {
        return [
            [
                'name' => 'Calculator',
                'description' => 'Computes mathematical expressions (e.g., "1 + 2", "5 * 3")',
                'example' => 'What is 234 * 567?'
            ],
            [
                'name' => 'Weather',
                'description' => 'Gets current weather information for any location',
                'example' => 'What\'s the weather in London?'
            ],
            [
                'name' => 'Email',
                'description' => 'Sends emails to specified recipients',
                'example' => 'Send an email to example@email.com with subject "Hello" and message "This is a test email"'
            ],
            [
                'name' => 'Time',
                'description' => 'Provides time-related information (e.g., "What time is it?", "What\'s the current date?")',
                'example' => 'What time is it in Tokyo?'
            ],
            [
                'name' => 'Eloquent',
                'description' => 'Execute Eloquent queries on Laravel models',
                'example' => 'Show me all users ordered by created_at'
            ],
            [
                'name' => 'Crypto',
                'description' => 'Get current cryptocurrency prices and market data',
                'example' => 'What\'s the current price of Bitcoin?'
            ]
        ];
    }

    protected function getToolResultsByType($toolResults, $steps): array
    {
        $resultsByType = [];

        if (!$steps) {
            return $resultsByType;
        }

        $stepsArray = is_array($steps) ? $steps : $steps->toArray();

        foreach ($stepsArray as $step) {
            if (is_object($step) && property_exists($step, 'toolCalls') && property_exists($step, 'toolResults')) {
                foreach ($step->toolResults as $index => $result) {
                    $toolName = isset($step->toolCalls[$index]) ? $step->toolCalls[$index]->name : 'unknown';
                    if (!isset($resultsByType[$toolName])) {
                        $resultsByType[$toolName] = [];
                    }
                    $resultsByType[$toolName][] = $result->result;
                }
            }
        }

        return $resultsByType;
    }

    protected function buildEmailContent(array $resultsByType): string
    {
        $content = '';

        // Order matters for readability
        $toolOrder = ['time', 'weather', 'crypto', 'calculator'];

        foreach ($toolOrder as $tool) {
            if (isset($resultsByType[$tool])) {
                foreach ($resultsByType[$tool] as $result) {
                    $content .= $result . "\n\n";
                }
            }
        }

        // Add any other tool results not in the predefined order
        foreach ($resultsByType as $tool => $results) {
            if (!in_array($tool, $toolOrder)) {
                foreach ($results as $result) {
                    $content .= $result . "\n\n";
                }
            }
        }

        return trim($content);
    }

    protected function getEmailSubject(array $resultsByType): string
    {
        $toolNames = array_keys($resultsByType);

        if (count($toolNames) === 1) {
            $tool = $toolNames[0];
            $subjects = [
                'weather' => 'Weather Update',
                'time' => 'Time Information',
                'crypto' => 'Cryptocurrency Price Update',
                'calculator' => 'Calculation Result'
            ];
            return $subjects[$tool] ?? 'Information Update';
        }

        return 'Multiple Information Update';
    }

    protected function sendCompoundEmail(string $subject, string $content): ?string
    {
        if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $this->newMessage, $matches)) {
            try {
                return $this->tools['email']($matches[0], $subject, $content);
            } catch (\Exception $e) {
                Log::error('Failed to send email:', ['error' => $e->getMessage()]);
            }
        }
        return null;
    }

    public function mount()
    {
        // Set available tools
        $this->availableTools = $this->getAvailableTools();

        // Initialize with a welcome message
        $this->messages[] = [
            'role' => 'assistant',
            'content' => 'Hello! I\'m your AI assistant. I can help you with calculations, weather information, emails, time-related queries, Eloquent queries, and cryptocurrency prices. Try asking me something!'
        ];
    }

    public function sendMessage()
    {
        if (empty($this->newMessage)) {
            return;
        }

        // Reset debug info
        $this->debugInfo = [
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

        // Add user message to chat
        $this->messages[] = [
            'role' => 'user',
            'content' => $this->newMessage
        ];

        $this->isProcessing = true;

        try {
            // Initialize tools
            $calculator = new CalculatorTool();
            $weather = new WeatherTool();
            $email = new EmailTool();
            $time = new TimeTool();
            $eloquent = new EloquentTool();
            $crypto = new CryptoTool();

            // Store tools for later use
            $this->tools = [
                'calculator' => $calculator,
                'weather' => $weather,
                'email' => $email,
                'time' => $time,
                'eloquent' => $eloquent,
                'crypto' => $crypto
            ];

            Log::info('Starting Prism request with message:', [
                'message' => $this->newMessage
            ]);

            // Get AI response using Prism
            $response = Prism::text()
                ->using(Provider::OpenAI, 'gpt-4o-mini')
                ->withSystemPrompt('You are a helpful AI assistant that can help with calculations, weather information, emails, time-related queries, Eloquent queries, and cryptocurrency prices.

IMPORTANT: When handling requests that require multiple pieces of information:
1. You MUST call each required tool separately and in sequence
2. For weather information, ALWAYS use the weather tool with the location
3. For cryptocurrency prices, ALWAYS use the crypto tool with the coin name
4. For time information, ALWAYS use the time tool with the timezone
5. Only after collecting ALL required information, use the email tool to send the combined results

Example flow for "send weather and crypto price":
1. Call weather tool for weather data
2. Call crypto tool for price data
3. Call email tool with combined results

Never skip any required tool calls. Always get fresh data from each tool.')
                ->withPrompt($this->newMessage)
                ->withTools([$calculator, $weather, $email, $time, $eloquent, $crypto])
                ->generate();

            Log::info('Raw Prism Response:', [
                'response' => $response
            ]);

            $toolResults = [];
            $weatherData = null;
            $emailSent = false;
            $lastToolCall = '';

            // Process tool results from steps
            if (property_exists($response, 'steps')) {
                foreach ($response->steps as $step) {
                    if (property_exists($step, 'toolCalls')) {
                        foreach ($step->toolCalls as $call) {
                            $lastToolCall = $call->name;
                        }
                    }
                    if (property_exists($step, 'toolResults')) {
                        foreach ($step->toolResults as $result) {
                            $toolResults[] = $result->result;
                        }
                    }
                }
            }

            // Handle compound email tasks
            if (!empty($toolResults) && str_contains(strtolower($this->newMessage), 'email')) {
                $resultsByType = $this->getToolResultsByType($toolResults, $response->steps);

                if (!empty($resultsByType)) {
                    Log::info('Completing compound task - sending email with multiple results', [
                        'tools_used' => array_keys($resultsByType)
                    ]);

                    $emailContent = $this->buildEmailContent($resultsByType);
                    $emailSubject = $this->getEmailSubject($resultsByType);

                    $emailResult = $this->sendCompoundEmail($emailSubject, $emailContent);
                    if ($emailResult) {
                        $toolResults[] = $emailResult;
                        $emailSent = true;
                    }
                }
            }

            // Store debug info with only serializable data
            $this->debugInfo = [
                'text' => $response->text ?? '',
                'has_steps' => property_exists($response, 'steps'),
                'steps_count' => property_exists($response, 'steps') ? count($response->steps) : 0,
                'raw_response' => json_encode($response),
                'last_tool' => $lastToolCall,
                'compound_task_completed' => $emailSent,
                'tool_results' => $toolResults,
                'processing_time' => microtime(true) - LARAVEL_START,
                'email_sent' => $emailSent,
                'last_tool_result' => end($toolResults),
                'total_tokens' => property_exists($response, 'usage') ?
                    ($response->usage->promptTokens + $response->usage->completionTokens) : 0
            ];

            Log::info('Tool Results Summary:', [
                'count' => count($toolResults),
                'results' => $toolResults,
                'weather_data' => $weatherData,
                'email_sent' => $emailSent,
                'last_tool' => $lastToolCall
            ]);

            // If we have tool results, show them
            if (!empty($toolResults)) {
                Log::info('Formatting tool results with LLM');

                // Create a prompt that includes the original query and tool results
                $formatPrompt = "Original user query: \"{$this->newMessage}\"\n\n";
                $formatPrompt .= "Tool results:\n";
                foreach ($toolResults as $result) {
                    $formatPrompt .= "- {$result}\n";
                }
                $formatPrompt .= "\nCreate a natural, concise response that incorporates these results. Do not add any extra commentary about helping or asking if they need anything else.";

                // Get formatted response using Prism
                $formattedResponse = Prism::text()
                    ->using(Provider::OpenAI, 'gpt-4o-mini')
                    ->withSystemPrompt('You are a helpful assistant that creates natural, concise responses. Format the tool results into a single coherent response without adding any extra commentary.')
                    ->withPrompt($formatPrompt)
                    ->generate();

                // Add the formatted response to chat
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $formattedResponse->text
                ];
            } elseif (!empty($response->text)) {
                Log::info('Adding AI response to chat:', [
                    'text' => $response->text
                ]);
                // If we have an AI response but no tool results, show the AI response
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $response->text
                ];
            } else {
                Log::info('No results found, showing error message');
                // No tool results and no AI response
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => "I apologize, but I wasn't able to perform that calculation. Please try rephrasing your question."
                ];
            }
        } catch (\Exception $e) {
            Log::error('Chat Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->messages[] = [
                'role' => 'system',
                'content' => 'I encountered an error while processing your request: ' . $e->getMessage()
            ];
        }

        $this->isProcessing = false;
        $this->newMessage = '';
    }

    public function render()
    {
        return view('toolbox::chat');
    }
}
