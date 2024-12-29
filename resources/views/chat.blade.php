<div class="flex h-screen bg-gray-100">
    <!-- Tools Sidebar -->
    <div class="w-64 bg-white border-r border-gray-200 p-4">
        <h2 class="text-lg font-semibold mb-4">Available Tools</h2>
        <div class="space-y-4">
            @foreach($availableTools as $tool)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <h3 class="font-medium text-gray-900">{{ $tool['name'] }}</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ $tool['description'] }}</p>
                    <div class="mt-2">
                        <span class="text-xs font-medium text-gray-500">Example:</span>
                        <p class="text-sm text-blue-600 italic">{{ $tool['example'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Chat Area -->
    <div class="flex-1 flex flex-col">
        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
            @foreach($messages as $message)
                <div class="flex @if($message['role'] === 'user') justify-end @endif">
                    <div class="max-w-[80%] @if($message['role'] === 'user') bg-blue-500 text-white @elseif($message['role'] === 'tool') bg-gray-200 text-gray-800 @else bg-gray-100 text-gray-800 @endif rounded-lg px-4 py-2 shadow">
                        @if($message['role'] === 'tool')
                            <div class="text-xs text-gray-500 mb-1">Tool Result</div>
                        @endif
                        <div class="prose max-w-none">
                            {!! Str::markdown($message['content']) !!}
                        </div>
                        @if($message['role'] === 'tool' && isset($message['debug']))
                            <div class="mt-2 text-xs text-gray-500 border-t pt-2">
                                <div>Tool: {{ $message['debug']['name'] }}</div>
                                <div>Arguments: {{ json_encode($message['debug']['arguments'], JSON_PRETTY_PRINT) }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            @if($isProcessing)
                <div class="flex">
                    <div class="bg-gray-100 rounded-lg px-4 py-2 shadow">
                        <div class="flex items-center space-x-2">
                            <div class="animate-bounce">⋯</div>
                            <span class="text-gray-600">Thinking...</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Debug Information -->
        @if(!empty($debugInfo))
            <div class="border-t border-gray-200 p-4">
                <div class="text-sm text-gray-700">
                    <details class="mb-2">
                        <summary class="font-semibold cursor-pointer">Debug Information</summary>
                        <div class="mt-2 space-y-2">
                            <div class="bg-gray-50 p-2 rounded">
                                <div class="font-medium">Response Info:</div>
                                <div class="text-xs">
                                    <div>Text: {{ $debugInfo['text'] }}</div>
                                    <div>Has Steps: {{ $debugInfo['has_steps'] ? 'Yes' : 'No' }}</div>
                                    <div>Steps Count: {{ $debugInfo['steps_count'] }}</div>
                                    <div>Last Tool: {{ $debugInfo['last_tool'] }}</div>
                                    <div>Processing Time: {{ number_format($debugInfo['processing_time'], 4) }}s</div>
                                    <div>Total Tokens: {{ $debugInfo['total_tokens'] }}</div>
                                </div>
                            </div>

                            @if(!empty($debugInfo['tool_results']))
                                <div class="bg-gray-50 p-2 rounded">
                                    <div class="font-medium">Tool Results:</div>
                                    @foreach($debugInfo['tool_results'] as $index => $result)
                                        <div class="text-xs mt-1 border-l-2 border-blue-300 pl-2">
                                            <div>Result {{ $index + 1 }}:</div>
                                            <pre class="whitespace-pre-wrap">{{ $result }}</pre>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="bg-gray-50 p-2 rounded">
                                <div class="font-medium">Task Info:</div>
                                <div class="text-xs">
                                    <div>Email Sent: {{ $debugInfo['email_sent'] ? 'Yes' : 'No' }}</div>
                                    <div>Compound Task: {{ $debugInfo['compound_task_completed'] ? 'Yes' : 'No' }}</div>
                                </div>
                            </div>

                            @if($debugInfo['last_tool_result'])
                                <div class="bg-gray-50 p-2 rounded">
                                    <div class="font-medium">Last Tool Result:</div>
                                    <div class="text-xs">
                                        <pre class="whitespace-pre-wrap">{{ $debugInfo['last_tool_result'] }}</pre>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </details>
                </div>
            </div>
        @endif

        <!-- Input Area -->
        <div class="border-t p-4">
            <form wire:submit="sendMessage" class="flex space-x-2">
                <input 
                    type="text" 
                    wire:model="newMessage" 
                    placeholder="Type your message..." 
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @if($isProcessing) disabled @endif
                >
                <button 
                    type="submit" 
                    class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                    @if($isProcessing) disabled @endif
                >
                    Send
                </button>
            </form>
        </div>
    </div>
</div>

@script
<script>
    const container = document.getElementById('chat-messages');
    
    // Auto-scroll to bottom when new messages arrive
    $wire.on('messageAdded', () => {
        container.scrollTop = container.scrollHeight;
    });
</script>
@endscript
