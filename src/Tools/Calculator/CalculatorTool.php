<?php

namespace JordanPrice\Toolbox\Tools\Calculator;

use EchoLabs\Prism\Tool;
use Illuminate\Support\Facades\Log;
use JordanPrice\Toolbox\Tools\Calculator\Calculator;

class CalculatorTool extends Tool
{
    protected Calculator $calculator;

    public function __construct()
    {
        $this->calculator = new Calculator();

        $this
            ->as('calculator')
            ->for('Computes mathematical expressions')
            ->withParameter('expression', 'The mathematical expression to evaluate (e.g., "1 + 2", "5 * 3")')
            ->using($this);
    }

    public function __invoke(string $expression): string
    {
        try {
            Log::info('Calculator Tool Input:', ['expression' => $expression]);

            $result = $this->calculator->evaluate($expression);
            $response = $this->calculator->formatResult($expression, $result);

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
}
