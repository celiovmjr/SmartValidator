# SmartValidator

The **SmartValidator** is a PHP class designed to validate and filter input data based on configurable rules. It supports a variety of simple and complex validations, ensuring that data meets specified criteria before being considered valid.

## Installation

To use the SmartValidator, include the `SmartValidator.php` class file in your PHP project and instantiate the class as needed.

```php
require_once 'SmartValidator.php';

// Basic usage example
$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30,
];

$rules = [
    'name' => 'string|required',
    'email' => 'email|required',
    'age' => 'int|required|min:18',
];

$validator = new Validator\Application\SmartValidator($data, $rules);
$validatedData = $validator->getValidated();

// $validatedData will contain the validated data according to the specified rules
```

## Features

### Constructor

```php
public function __construct(array|object $data, array $rules)
```

- **$data**: Associative array containing the data to be validated.
- **$rules**: Associative array where keys represent property names to validate, and values are strings containing validation rules separated by pipe (`|`).

### Supported Validation Methods

The SmartValidator supports the following types of validation:

- **Simple Types**:
  - `string`: Validates if the value is a string and applies sanitization.
  - `bool`: Validates and converts the value to boolean.
  - `int`: Validates and converts the value to integer.
  - `float`: Validates and converts the value to float.
  - `email`: Validates if the value is a valid email address.
  - `url`: Validates if the value is a valid URL.
  - `ip`: Validates if the value is a valid IP address.
  - `required`: Validates if the value is not null or empty.
  - `uuid`: Validates if the value is a valid UUID.

- **Complex Validations**:
  - `min:x`: Validates if the numeric value is greater than or equal to `x`, or if the string has at least `x` characters.
  - `max:x`: Validates if the numeric value is less than or equal to `x`, or if the string has at most `x` characters.
  - `range:min,max`: Validates if the value is within the specified range.
  - `format:format`: Validates if the value matches the specified date format.
  - `before:date,format`: Validates if the value is a date before the specified date.
  - `after:date,format`: Validates if the value is a date after the specified date.
  - `in:val1,val2,...`: Validates if the value is among the allowed values.
  - `size:size`: Validates if the size of the value is equal to `size`.
  - `mime:type1,type2,...`: Validates if the MIME type of the value is among the allowed types.

### Exceptions

The SmartValidator throws `InvalidArgumentException` when a validation fails, with detailed error messages explaining the reason for the validation failure.

### Return

- **getValidated()**: Returns an associative array containing successfully validated data according to the specified rules.

```php
public function getValidated(): array
```

## Final Notes

The SmartValidator offers a flexible solution for data validation in PHP, providing a robust mechanism to ensure input data meets defined requirements before processing or storing. It facilitates the implementation of complex validations in a clear and structured manner, promoting data security and integrity in PHP applications.
