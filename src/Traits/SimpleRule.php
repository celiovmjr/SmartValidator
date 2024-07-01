<?php declare(strict_types= 1);

namespace Validator\Application\Traits;
use InvalidArgumentException;

trait SimpleRule
{
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
            'uuid' => $this->validateUuid($value),
            'nullable' => filter_var($value, FILTER_UNSAFE_RAW),
            'required' => $this->validateRequired($value, $property),
            default => throw new InvalidArgumentException("A regra '$rule' não existe.")
        };
    }

    private function validateUuid(string $uuid): ?string
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid)) {
            return $uuid;
        }

        throw new InvalidArgumentException("O valor fornecido não é um 'UUID' válido.");
    }

    private function validateRequired(mixed $value, string $property): mixed
    {
        $isValid = match (gettype($value)) {
            'boolean', 'integer', 'double', 'resource' => true,
            'string' => $value !== '',
            'array' => !empty($value),
            'object' => !empty(get_object_vars($value)),
            default => false
        };

        if ($isValid) {
            return $value;
        }

        throw new InvalidArgumentException("O campo '$property' é obrigatório.");
    }
}