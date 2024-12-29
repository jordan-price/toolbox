<?php

namespace JordanPrice\Toolbox\Tools\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Eloquent
{
    protected array $allowedOperations = [
        'select', 'where', 'orderBy', 'limit', 
        'first', 'get', 'count', 'avg', 
        'sum', 'min', 'max'
    ];
    protected array $allowedModels = [];

    /**
     * Execute a query on a model
     */
    public function query(string $model, string $operation, array $parameters = []): mixed
    {
        // Validate inputs
        $this->validateOperation($operation);
        $modelClass = $this->validateModel($model);

        // Build and execute query
        return $this->buildQuery($modelClass, $operation, $parameters);
    }

    /**
     * Format the query result into a readable string
     */
    public function formatResult(mixed $result): string
    {
        return "Query executed successfully.\nResult: " . json_encode($result, JSON_PRETTY_PRINT);
    }

    /**
     * Get list of allowed models
     */
    public function getAllowedModels(): array
    {
        return array_map(fn($class) => class_basename($class), $this->allowedModels);
    }

    /**
     * Get list of allowed operations
     */
    public function getAllowedOperations(): array
    {
        return $this->allowedOperations;
    }

    /**
     * Add a model to the allowed list
     */
    public function addAllowedModel(string $modelClass): void
    {
        if (!in_array($modelClass, $this->allowedModels)) {
            $this->allowedModels[] = $modelClass;
        }
    }

    /**
     * Validate the requested operation
     */
    protected function validateOperation(string $operation): void
    {
        if (!in_array($operation, $this->allowedOperations)) {
            throw new \InvalidArgumentException(
                "Operation '{$operation}' is not allowed. Allowed operations: " . 
                implode(', ', $this->allowedOperations)
            );
        }
    }

    /**
     * Validate and get the full model class name
     */
    protected function validateModel(string $model): string
    {
        $modelClass = "App\\Models\\{$model}";

        if (!in_array($modelClass, $this->allowedModels)) {
            throw new \InvalidArgumentException(
                "Model '{$model}' not found or not allowed. Available models: " . 
                implode(', ', $this->getAllowedModels())
            );
        }

        return $modelClass;
    }

    /**
     * Build and execute the query
     */
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
}
