<?php

namespace JordanPrice\Toolbox\Tools\Eloquent;

use EchoLabs\Prism\Tool;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class EloquentTool extends Tool
{
    protected Eloquent $eloquent;

    public function __construct()
    {
        $this->eloquent = new Eloquent();

        $this
            ->as('eloquent')
            ->for('Execute Eloquent queries on Laravel models')
            ->withParameter('model', 'The model name to query (e.g., User, Post)')
            ->withParameter('operation', sprintf(
                'The operation to perform (%s)',
                implode(', ', $this->eloquent->getAllowedOperations())
            ))
            ->withParameter('parameters', 'Parameters for the operation')
            ->using($this);

        // Automatically discover available models
        $this->discoverModels();
    }

    protected function discoverModels(): void
    {
        $modelsPath = app_path('Models');
        Log::info('Discovering models in path:', ['path' => $modelsPath]);

        if (!file_exists($modelsPath)) {
            Log::warning('Models directory does not exist:', ['path' => $modelsPath]);
            return;
        }

        foreach (glob("{$modelsPath}/*.php") as $file) {
            $className = 'App\\Models\\' . pathinfo($file, PATHINFO_FILENAME);
            Log::info('Found potential model:', ['class' => $className]);

            if (class_exists($className) && (is_subclass_of($className, Model::class) || is_subclass_of($className, Authenticatable::class))) {
                $this->eloquent->addAllowedModel($className);
                Log::info('Added model to allowed list:', ['class' => $className]);
            }
        }

        Log::info('Discovered models:', ['models' => $this->eloquent->getAllowedModels()]);
    }

    public function __invoke(string $model, string $operation, array $parameters = []): string
    {
        try {
            Log::info('Eloquent Tool Input:', [
                'model' => $model,
                'operation' => $operation,
                'parameters' => $parameters
            ]);

            $result = $this->eloquent->query($model, $operation, $parameters);
            $response = $this->eloquent->formatResult($result);

            Log::info('Eloquent Tool Output:', ['response' => $response]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Eloquent Tool Error:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
