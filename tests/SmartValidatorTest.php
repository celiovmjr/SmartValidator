<?php

declare(strict_types=1);

namespace Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Validator\Application\SmartValidator;

class SmartValidatorTest extends TestCase
{
    public function testValidData(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 30,
            'email' => 'john.doe@example.com',
        ];
        $rules = [
            'name' => 'required|string',
            'age' => 'required|int|min:18',
            'email' => 'required|email',
        ];

        $validator = new SmartValidator($data, $rules);

        $this->assertEquals($data, $validator->getValidated());
    }

    public function testAgeMinInvalid(): void
    {
        $data = ['age' => 17];
        $rules = ['age' => 'required|int|min:18'];

        $this->expectException(InvalidArgumentException::class);

        $validator = new SmartValidator($data, $rules);
    }

    public function testNullableField(): void
    {
        $data = ['name' => null];
        $rules = ['name' => 'nullable|string'];

        $validator = new SmartValidator($data, $rules);

        $this->assertNull($validator->getValidated()['name']);
    }

    public function testStringMaxInvalid(): void
    {
        $data = ['username' => 'toolongusername'];
        $rules = ['username' => 'required|string|max:10'];

        $this->expectException(InvalidArgumentException::class);

        $validator = new SmartValidator($data, $rules);
    }

    public function testDateBeforeInvalid(): void
    {
        $data = ['event_date' => '2023-01-01'];
        $rules = ['event_date' => 'required|date|before:2022-12-31'];

        $this->expectException(InvalidArgumentException::class);

        $validator = new SmartValidator($data, $rules);
    }

    public function testInConstraintValid(): void
    {
        $data = ['role' => 'admin'];
        $rules = ['role' => 'required|string|in:user,admin,editor'];
        $validator = new SmartValidator($data, $rules);

        $this->assertEquals('admin', $validator->getValidated()['role']);
    }
}

