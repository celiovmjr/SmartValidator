<?php

declare(strict_types=1);

namespace Validator\Application;

use InvalidArgumentException;

class SmartValidator
{
    private array $rules = [];
    private array $data = [];
    private array $validated = [];

    public function __construct(
        array|object $data,
        array $rules
    ) {
        settype($data, 'array');
        $this->extractRules($data, $rules);
    }

    private function extractRules(array $data, array $rules): void
    {
        $this->data = $data;
        $this->rules = $rules;

        foreach ($rules as $property => $rawRules) {
            if (!array_key_exists($property, $data)) {
                continue;
            }

            $rules = explode('|', $rawRules);

            if (empty($rules)) {
                throw new InvalidArgumentException("As regras fornecidas são inválidas para o campo '$property'.");
            }

            if (str_contains($rawRules, 'nullable') && is_null($data[$property])) {
                $this->validated[$property] = null;
                continue;
            }

            foreach ($rules as $rule) {
                if (! $pos = strpos($rule, ':')) {
                    $this->validated[$property] = $this->applySimpleRule($property, $rule, $data[$property]);
                    continue;
                }

                $ruleName = substr($rule, 0, $pos);
                $ruleValue = substr($rule, $pos + 1);
                $this->validated[$property] = $this->applyComplexRule($property, $ruleName, $ruleValue, $data[$property]);
            }
        }
    }

    private function applySimpleRule(string $property, string $rule, mixed $value): mixed
    {
        return match ($rule) {
            'string' => filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS),
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'int' => filter_var($value, FILTER_VALIDATE_INT),
            'float' => filter_var($value, FILTER_VALIDATE_FLOAT),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL),
            'url' => filter_var($value, FILTER_VALIDATE_URL),
            'ip' => filter_var($value, FILTER_VALIDATE_IP),
            'required' => $this->validateRequired($value, $property),
            'uuid' => $this->validateUuid($value, $rule),
            default => throw new InvalidArgumentException("A regra '{$rule}' não existe.")
        };
    }

    private function applyComplexRule(string $property, string $ruleName, string $ruleValue, mixed $value): mixed
    {
        switch ($ruleName) {
            case 'min':
                return $this->validateMin($value, (int)$ruleValue, $property);
            case 'max':
                return $this->validateMax($value, (int)$ruleValue, $property);
            case 'range':
                $rangeValues = explode(',', $ruleValue);
                return $this->validateRange($value, $rangeValues[0], $rangeValues[1], $property);
            case 'format':
                return $this->validateDateFormat($value, $ruleValue, $property);
            case 'before':
                $beforeValues = explode(',', $ruleValue);
                return $this->validateBefore($value, $beforeValues[0], $beforeValues[1], $property);
            case 'after':
                $afterValues = explode(',', $ruleValue);
                return $this->validateAfter($value, $afterValues[0], $afterValues[1], $property);
            case 'in':
                $allowedValues = explode(',', $ruleValue);
                return $this->validateIn($value, $allowedValues, $property);
            case 'size':
                return $this->validateSize($value, (int)$ruleValue, $property);
            case 'mime':
                $allowedMimes = explode(',', $ruleValue);
                return $this->validateMime($value, $allowedMimes, $property);
            default:
                throw new InvalidArgumentException("A regra '{$ruleName}' não existe.");
        }
    }

    private function validateRequired(mixed $value, string $property): mixed
    {
        $isValid = match (gettype($value)) {
            'boolean', 'integer', 'double' => true,
            'string' => $value !== '',
            'NULL' => false,
            'array' => !empty($value),
            'object' => !empty(get_object_vars($value)),
            'resource' => true,
            default => false,
        };

        if ($isValid) {
            return $value;
        }

        throw new InvalidArgumentException("O campo '{$property}' é obrigatório.");
    }

    private function validateUuid(string $uuid, string $rule): ?string
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid)) {
            return $uuid;
        }

        throw new InvalidArgumentException("O valor fornecido não é um '{$rule}' válido.");
    }

    private function validateMin(mixed $value, int $min, string $property): mixed
    {
        if (is_numeric($value)) {
            if ($value >= $min) {
                return $value;
            }

            throw new InvalidArgumentException("O valor para '{$property}' deve ser maior que '{$min}'");
        }

        if (is_string($value)) {
            if (mb_strlen($value) >= $min) {
                return $value;
            }

            throw new InvalidArgumentException("O valor para '{$property}' deve ter ao menos '{$min}' caracteres");
        }

        throw new InvalidArgumentException("A regra 'min:x' não é válida para o campo '{$property}'.");
    }

    private function validateMax(mixed $value, int $max, string $property): mixed
    {
        if (is_numeric($value) && $value <= $max) {
            return $value;
        }

        if (mb_strlen($value) <= $max) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '{$property}' deve atender ao máximo permitido.");
    }

    private function validateRange(mixed $value, string $min, string $max, string $property): mixed
    {
        if (is_numeric($value) && $value >= $min && $value <= $max) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '{$property}' deve estar entre {$min} e {$max}.");
    }

    private function validateDateFormat(string $value, string $format, string $property): string
    {
        $dateTime = \DateTime::createFromFormat($format, $value);

        if ($dateTime && $dateTime->format($format) === $value) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '{$property}' não está no formato '{$format}'.");
    }

    private function validateBefore(string $value, string $date, string $format, string $property): string
    {
        $dateTime = \DateTime::createFromFormat($format, $value);
        $beforeTime = \DateTime::createFromFormat($format, $date);

        if ($dateTime && $beforeTime && $dateTime < $beforeTime) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '{$property}' deve estar antes de '{$date}'.");
    }

    private function validateAfter(string $value, string $date, string $format, string $property): string
    {
        $dateTime = \DateTime::createFromFormat($format, $value);
        $afterTime = \DateTime::createFromFormat($format, $date);

        if ($dateTime && $afterTime && $dateTime > $afterTime) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '{$property}' deve estar após '{$date}'.");
    }

    private function validateIn(mixed $value, array $allowedValues, string $property): mixed
    {
        if (in_array($value, $allowedValues, true)) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '{$property}' deve ser um dos seguintes: " . implode(', ', $allowedValues) . ".");
    }

    private function validateSize(mixed $value, int $size, string $property): mixed
    {
        $actualSize = 0;

        if (is_string($value)) {
            $actualSize = mb_strlen($value, '8bit');
        } elseif (is_array($value)) {
            $actualSize = count($value);
        } elseif (is_object($value) && $value instanceof \Countable) {
            $actualSize = count($value);
        }

        if ($actualSize === $size) {
            return $value;
        }

        throw new InvalidArgumentException("O tamanho para '{$property}' deve ser {$size}.");
    }

    private function validateMime(mixed $value, array $allowedMimes, string $property): mixed
    {
        $mime = mime_content_type($value);

        if ($mime && in_array($mime, $allowedMimes, true)) {
            return $value;
        }

        throw new InvalidArgumentException("O MIME type para '{$property}' deve ser um dos seguintes: " . implode(', ', $allowedMimes) . ".");
    }

    public function getValidated(): array
    {
        return $this->validated;
    }
}
