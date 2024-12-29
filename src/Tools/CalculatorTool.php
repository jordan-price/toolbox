<?php

namespace App\Tools;

use EchoLabs\Prism\Tool;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class CalculatorTool extends Tool
{
    public function __construct()
    {
        $this
            ->as('calculator')
            ->for('Computes mathematical expressions')
            ->withStringParameter('expression', 'The mathematical expression to evaluate (e.g., "1 + 2", "5 * 3")')
            ->using($this);
    }

    public function __invoke(string $expression): string
    {
        try {
            Log::info('Calculator Tool Input:', ['expression' => $expression]);

            // Sanitize and validate the expression
            $sanitizedExpr = $this->sanitizeExpression($expression);
            
            // Evaluate the expression
            $result = $this->evaluateExpression($sanitizedExpr);
            
            $response = "Result of {$expression} = {$result}";
            Log::info('Calculator Tool Output:', ['response' => $response]);
            
            return $response;

        } catch (\Exception $e) {
            Log::error('Calculator Tool Error:', [
                'expression' => $expression,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function sanitizeExpression(string $expr): string
    {
        // Remove any whitespace
        $expr = trim($expr);
        
        // Only allow basic mathematical operations and numbers
        if (!preg_match('/^[\d\s\+\-\*\/\(\)\.\^]+$/', $expr)) {
            throw new InvalidArgumentException('Expression contains invalid characters. Only numbers and basic operators (+, -, *, /, ^) are allowed.');
        }

        return $expr;
    }

    private function evaluateExpression(string $expr): float
    {
        // Replace ^ with ** for exponentiation
        $expr = str_replace('^', '**', $expr);
        
        // Create a safe mathematical context
        $safeExpr = '$result = ' . $expr . ';';
        
        try {
            $result = null;
            eval($safeExpr);
            
            if (!is_numeric($result)) {
                throw new InvalidArgumentException('Invalid mathematical expression');
            }
            
            return (float) $result;
        } catch (\ParseError $e) {
            throw new InvalidArgumentException('Invalid mathematical expression: ' . $e->getMessage());
        }
    }
}
