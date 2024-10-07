<?php

declare(strict_types=1);

namespace Validator\Application;

use InvalidArgumentException;
use Validator\Application\Traits\SimpleRule;
use Validator\Application\Traits\ComplexRule;

class SmartValidator
{
    use SimpleRule, ComplexRule;

    private array $validated = [];

    public function __construct(array $data, array $rules)
    {
        $this->validateData($data, $rules);
    }

    private function validateData(array $data, array $rules): void
    {
        foreach ($rules as $property => $rawRules) {
            if (!array_key_exists($property, $data)) {
                continue;
            }

            $rules = explode('|', $rawRules);

            if (empty($rules)) {
                throw new InvalidArgumentException("As regras fornecidas são inválidas para o campo '$property'.");
            }

            if (str_contains($rawRules, "nullable") && is_null($data[$property])) {
                $this->validated[$property] = null;
                continue;
            }

            if (is_null($data[$property])) {
                throw new InvalidArgumentException("O campo '$property' não pode ser nulo.");
            }

            foreach ($rules as $rule) {
                if (!str_contains($rule, ':')) {
                    $this->validated[$property] = $this->applySimpleRule($property, $rule, $data[$property]);
                    settype($this->validated[$property], $rules[0]);
                    continue;
                }
            
                $pos = strpos($rule, ':');
                $ruleName = substr($rule, 0, $pos);
                $ruleValue = substr($rule, $pos + 1);
                $this->validated[$property] = $this->applyComplexRule($property, $ruleName, $ruleValue, $data[$property]);
                settype($this->validated[$property], $rules[0]);
            }
        }
    }
}
