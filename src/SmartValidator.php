<?php

declare(strict_types=1);

namespace Validator\Application;

use DateTime;
use InvalidArgumentException;

class SmartValidator
{
    private array $validated = [];
    private array $formats = [
        'Y-m-d',
        'Y-m',
        'd/m/Y',
        'm/Y',
        'Y-m-d H:i:s',
        'd/m/Y H:i:s',
        'Ymd',
        'd-m-Y',
        'd-M-Y',
        'Y-m-d\TH:i:s.u',
        'Y-m-d\TH:i:sP',
        'Y-m-d\TH:i:sO',
        'Y-m-d\TH:i:s',
        'Y-m-d\TH:i:s.v',
        'd-M-Y H:i:s',
        'd-M-Y h:i:s A'
    ];

    public function __construct(
        private array|object $data = [],
        private array $rules = []
    ) {
        settype($this->data, 'array');
        $this->validateAndSanitize();
    }

    private function validateAndSanitize(): void
    {
        foreach ($this->rules as $property => $rule) {
            if (! isset($this->data[$property])) {
                continue;
            }
    
            $value = $this->data[$property];
            $camelCaseProperty = $this->toCamelCase($property);
    
            if (! isset($rule['type'])) {
                throw new InvalidArgumentException("Tipo não especificado para a propriedade '{$property}'.");
            }
    
            $validatedValue = $this->validatePropertyValue(
                $property,
                $value,
                $rule['type'],
                $rule['required']
            );

            $this->validated[$camelCaseProperty] = $validatedValue;
        }
    }

    private function validatePropertyValue(string $property, mixed $value, string $type, bool $required = false): mixed
    {
        if ($required && !is_numeric($value)) {
            throw new InvalidArgumentException("O campo '{$property}' é obrigatório.");
        }

        return match ($type) {
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'int' => filter_var($value, FILTER_VALIDATE_INT),
            'float' => filter_var($value, FILTER_VALIDATE_FLOAT),
            'string' => filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS),
            'DateTime' => $this->validateDateTime($value),
            'uuid' => $this->validateUuid($value),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL),
            'url' => filter_var($value, FILTER_VALIDATE_URL),
            'ip' => filter_var($value, FILTER_VALIDATE_IP),
            default => throw new InvalidArgumentException("Tipo '{$type}' não suportado para a propriedade.")
        };
    }

    private function validateDateTime(?string $value): ?DateTime
    {
        if (empty($value)) {
            return null;
        }

        foreach ($this->formats as $format) {
            $dateTime = DateTime::createFromFormat($format, $value);
            if ($dateTime !== false && $dateTime->format($format) === $value) {
                return $dateTime;
            }
        }
    
        return null;
    }

    private function validateUuid(string $uuid): ?string
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid)) {
            return $uuid;
        }
        return null;
    }

    private function toCamelCase(string $value): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $value))));
    }

    public function getValidated(): array
    {
        return $this->validated ?? [];
    }
}
