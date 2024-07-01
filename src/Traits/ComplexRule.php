<?php declare(strict_types= 1);

namespace Validator\Application\Traits;
use DateTime;
use InvalidArgumentException;

trait ComplexRule
{
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
                throw new InvalidArgumentException("A regra '$ruleName' não existe.");
        }
    }

    private function validateMin(mixed $value, int $min, string $property): string|int|float
    {
        if (is_numeric($value)) {
            if ($value >= $min) {
                return $value;
            }

            throw new InvalidArgumentException("O valor para '$property' deve ser maior que '$min'");
        }

        if (is_string($value)) {
            if (mb_strlen($value) >= $min) {
                return $value;
            }

            throw new InvalidArgumentException("O valor para '$property' deve ter ao menos '$min' caracteres");
        }

        throw new InvalidArgumentException("A regra 'min:x' não é válida para o campo '$property'.");
    }

    private function validateMax(mixed $value, int $max, string $property): mixed
    {
        if (is_numeric($value) && $value <= $max) {
            return $value;
        }

        if (mb_strlen($value) <= $max) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '$property' deve atender ao máximo permitido.");
    }

    private function validateRange(mixed $value, string $min, string $max, string $property): string|int|float
    {
        if (is_numeric($value) && $value >= $min && $value <= $max) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '$property' deve estar entre $min e $max.");
    }

    private function validateDateFormat(string $value, string $format, string $property): string
    {
        $dateTime = DateTime::createFromFormat($format, $value);

        if ($dateTime && $dateTime->format($format) === $value) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '$property' não está no formato '$format'.");
    }

    private function validateBefore(string $value, string $date, string $format, string $property): string
    {
        $dateTime = DateTime::createFromFormat($format, $value);
        $beforeTime = DateTime::createFromFormat($format, $date);

        if ($dateTime && $beforeTime && $dateTime < $beforeTime) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '$property' deve estar antes de '$date'.");
    }

    private function validateAfter(string $value, string $date, string $format, string $property): string
    {
        $dateTime = DateTime::createFromFormat($format, $value);
        $afterTime = DateTime::createFromFormat($format, $date);

        if ($dateTime && $afterTime && $dateTime > $afterTime) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '$property' deve estar após '$date'.");
    }

    private function validateIn(mixed $value, array $allowedValues, string $property): mixed
    {
        if (in_array($value, $allowedValues, true)) {
            return $value;
        }

        throw new InvalidArgumentException("O valor para '$property' deve ser um dos seguintes: " . implode(', ', $allowedValues) . ".");
    }

    private function validateSize(mixed $value, int $size, string $property): mixed
    {
        $actualSize = match (true) {
            is_string($value) => mb_strlen($value, '8bit'),
            is_array($value) || ($value instanceof Countable) => count($value),
            default => 0,
        };
    
        if ($actualSize === $size) {
            return $value;
        }
    
        throw new InvalidArgumentException("O tamanho para '$property' deve ser $size.");
    }

    private function validateMime(mixed $value, array $allowedMimes, string $property): mixed
    {
        $mime = mime_content_type($value);

        if ($mime && in_array($mime, $allowedMimes, true)) {
            return $value;
        }

        throw new InvalidArgumentException("O MIME type para '$property' deve ser um dos seguintes: " . implode(', ', $allowedMimes) . ".");
    }

    public function getValidated(bool $associative = false): array|object
    {
        return $associative ? $this->validated : (object) $this->validated;
    }
}