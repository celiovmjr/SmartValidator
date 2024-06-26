<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Validator\Application\SmartValidator;

class SmartValidatorTest extends TestCase
{
    private array $validData;
    private array $invalidData;
    private array $rules;

    protected function setUp(): void
    {
        parent::setUp();

        // Dados válidos para testes
        $this->validData = [
            'username' => 'john_doe',
            'email' => 'john.doe@example.com',
            'age' => 30,
            'dob' => '1990-01-01',
        ];

        // Dados inválidos para testes
        $this->invalidData = [
            'username' => 'joh',
            'email' => 'invalid-email',
            'age' => 15,
            'dob' => '1990-01-99', // Data inválida
        ];

        // Regras de validação
        $this->rules = [
            'username' => 'string|required|min:6',
            'email' => 'email|required',
            'age' => 'int|required|min:18',
            'dob' => 'string|required|format:Y-m-d',
        ];
    }

    public function testValidData(): void
    {
        $validator = new SmartValidator($this->validData, $this->rules);

        $validatedData = $validator->getValidated(true);

        // Verifica se todos os campos obrigatórios foram validados corretamente
        $this->assertArrayHasKey('username', $validatedData);
        $this->assertArrayHasKey('email', $validatedData);
        $this->assertArrayHasKey('age', $validatedData);
        $this->assertArrayHasKey('dob', $validatedData);
    }

    public function testInvalidData(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $validator = new SmartValidator($this->invalidData, $this->rules);

        // Deve lançar exceção de InvalidArgumentException devido aos dados inválidos
        $validator->getValidated(true);
    }
}
