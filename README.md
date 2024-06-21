# SmartValidator

The `SmartValidator` is a PHP class that facilitates data validation and sanitization based on specific rules defined for each property. It is useful in applications where ensuring data integrity and security before processing is crucial.

## Features

- **Data Type Validation:** Supports validation of types such as boolean, integer, float, string, datetime, UUID, email, URL, and IP address.
- **Data Sanitization:** Provides methods to sanitize strings and ensure potentially dangerous data is handled properly.
- **Flexibility in Date Formats:** Accepts a variety of date formats for validation, including ISO formats, common date formats, and custom formats.

## Basic Usage

### Instantiation

```php
use App\Core\SmartValidator;

// Example data and rules
$data = [
    'username' => 'john_doe',
    'email' => 'john.doe@example.com',
    'birthdate' => '1990-01-01',
];

$rules = [
    'username' => ['type' => 'string'],
    'email' => ['type' => 'email'],
    'birthdate' => ['type' => 'DateTime'],
];

// Creating an instance of SmartValidator
$validator = new SmartValidator($data, $rules);

// Getting validated data
$validatedData = $validator->getValidated();
```

## Technical Details

The class utilizes PHP's native features such as `filter_var` for validation and `DateTime` for date handling. String sanitization is performed using `FILTER_SANITIZE_SPECIAL_CHARS`.

## Supported Date Formats

The class supports the following date formats for validation:

- `Y-m-d`
- `Y-m`
- `d/m/Y`
- `m/Y`
- `Y-m-d H:i:s`
- `d/m/Y H:i:s`
- `Ymd`
- `d-m-Y`
- `d-M-Y`
- `Y-m-d\TH:i:s.u`
- `Y-m-d\TH:i:sP`
- `Y-m-d\TH:i:sO`
- `Y-m-d\TH:i:s`
- `Y-m-d\TH:i:s.v`
- `d-M-Y H:i:s`
- `d-M-Y h:i:s A`

## Contribution

Contributions are welcome! If you identify issues, have suggestions for improvements, or want to add new features, feel free to open an issue or submit a pull request.

## License

This project is licensed under the [MIT License](LICENSE).
