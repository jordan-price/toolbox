<?php

namespace JordanPrice\Toolbox\Tools;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use EchoLabs\Prism\Tool;
use Illuminate\Foundation\Auth\User as Authenticatable;

class EloquentTool extends Tool
{
    protected array $allowedOperations = ['select', 'where', 'orderBy', 'limit', 'first', 'get', 'count', 'avg', 'sum', 'min', 'max'];
    protected array $allowedModels = [];

    public function __construct()
    {
        $this
            ->as('eloquent')
            ->for('Execute Eloquent queries on Laravel models')
            ->withParameter('model', 'The model name to query (e.g., User, Post)')
            ->withParameter('operation', 'The operation to perform (e.g., select, where, orderBy)')
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
                $this->allowedModels[] = $className;
                Log::info('Added model to allowed list:', ['class' => $className]);
            }
        }

        Log::info('Discovered models:', ['models' => $this->allowedModels]);
    }

    protected function validateOperation(string $operation): void
    {
        if (!in_array($operation, $this->allowedOperations)) {
            throw new \InvalidArgumentException("Operation '{$operation}' is not allowed. Allowed operations: " . implode(', ', $this->allowedOperations));
        }
    }

    protected function validateModel(string $model): string
    {
        $modelClass = "App\\Models\\{$model}";
        Log::info('Validating model:', ['model' => $model, 'class' => $modelClass, 'allowed' => $this->allowedModels]);

        if (!in_array($modelClass, $this->allowedModels)) {
            Log::error('Model not found or not allowed:', ['model' => $model, 'class' => $modelClass]);
            throw new \InvalidArgumentException("Model '{$model}' not found or not allowed. Available models: " . implode(', ', array_map(fn($class) => class_basename($class), $this->allowedModels)));
        }
        return $modelClass;
    }

    protected function buildQuery(string $modelClass, string $operation, array $parameters)
    {
        $query = $modelClass::query();

        switch ($operation) {
            case 'select':
                $query->select($parameters);
                break;
            case 'where':
                $query->where(...$parameters);
                break;
            case 'orderBy':
                if (isset($parameters[0]['column'])) {
                    $query->orderBy($parameters[0]['column'], $parameters[0]['value'] ?? 'asc');
                } else {
                    $query->orderBy(...$parameters);
                }
                return $query->get();
            case 'limit':
                $query->limit($parameters[0]);
                break;
            case 'first':
                return $query->first();
            case 'get':
                return $query->get();
            case 'count':
                return $query->count();
            case 'avg':
                return $query->avg($parameters[0]);
            case 'sum':
                return $query->sum($parameters[0]);
            case 'min':
                return $query->min($parameters[0]);
            case 'max':
                return $query->max($parameters[0]);
        }

        return $query->get(); // Always get results by default
    }

    public function __invoke(string $model, string $operation, array $parameters = []): string
    {
        try {
            Log::info('Eloquent Tool Input:', [
                'model' => $model,
                'operation' => $operation,
                'parameters' => $parameters
            ]);

            // Validate inputs
            $this->validateOperation($operation);
            $modelClass = $this->validateModel($model);

            // Build and execute query
            $result = $this->buildQuery($modelClass, $operation, $parameters);

            // Format response
            $response = "Query executed successfully.\nResult: " . json_encode($result, JSON_PRETTY_PRINT);

            Log::info('Eloquent Tool Output:', ['response' => $response]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Eloquent Tool Error:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
