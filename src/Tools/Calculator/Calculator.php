<?php

namespace JordanPrice\Toolbox\Tools\Calculator;

use InvalidArgumentException;

class Calculator
{
    /**
     * Evaluate a mathematical expression
     */
    public function evaluate(string $expression): float
    {
        // Sanitize and validate the expression
        $sanitizedExpr = $this->sanitizeExpression($expression);

        // Evaluate the expression
        return $this->evaluateExpression($sanitizedExpr);
    }

    /**
     * Format the result into a human-readable string
     */
    public function formatResult(string $expression, float $result): string
    {
        return "Result of {$expression} = {$result}";
    }

    /**
     * Sanitize and validate the expression
     */
    protected function sanitizeExpression(string $expr): string
    {
        // Remove any whitespace
        $expr = trim($expr);

        // Only allow basic mathematical operations and numbers
        if (!preg_match('/^[\d\s\+\-\*\/\(\)\.\^]+$/', $expr)) {
            throw new InvalidArgumentException('Expression contains invalid characters. Only numbers and basic operators (+, -, *, /, ^) are allowed.');
        }

        return $expr;
    }

    /**
     * Evaluate the mathematical expression
     */
    protected function evaluateExpression(string $expr): float
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
