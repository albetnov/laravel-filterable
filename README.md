# Query String based filter for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/albetnov/laravel-filterable.svg?style=flat-square)](https://packagist.org/packages/albetnov/laravel-filterable)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/albetnov/laravel-filterable/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/albetnov/laravel-filterable/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/albetnov/laravel-filterable/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/albetnov/laravel-filterable/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/albetnov/laravel-filterable.svg?style=flat-square)](https://packagist.org/packages/albetnov/laravel-filterable)

A Laravel Model Filterable to automatically filter a model based on given query string
## Installation

You can install the package via composer:

```bash
composer require albetnov/laravel-filterable
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-filterable-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

Simply add `Filterable` trait in your model and define either `$rows` or `getRows()` to define filterable columns:

```php
<?php

namespace App\Models;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model {
    use Filterable;
    
    protected arrray $rows = [
        'ticket_no' => FilterableType::NUMBER,
        'customer_name' => FilterableType::TEXT,
        'schedule' => FilterableType::DATE
    ];
}
```

### Filterable Type

There are three FilterableType options available:

- Number <br />
This type will cast the given payload to either float or int, depending on whether the string contains a . prefix (indicating a float) or not (indicating an int).

- Text <br />
A type designed for handling text-based filters.

- Date <br />
A type intended for handling date-based filters. This will cast the payload to the Carbon format and adjust the query accordingly.

After that, you can use your model this way:

```php
<?php

use App\Models\Flight;

// In this example I choose to merge the request, alternatively you can hit endpoint like this:
// http://localhost:8000/all-flights?filters[0][field]=customer_name&filters[0][operator]=eq&filters[0][value]=asep
request()->merge([
    'filters' => [
        [
            'operator' => 'eq',
            'field' => 'customer_name', 
            'value' => 'asep'
        ]
    ]
]);

dd(Flight::filter()->get()); // Flight[{customer_name: "asep", ticket_no: 20393, schedule: "2023-08-20"}]
```

## Request Schema

Here is the expected request payload schema that can be read by Laravel Filterable:

```json
{
  "filters": [
    {
        "operator": "eq",
        "field": "customer_name",
        "value": "asep"
    }
  ]
}
```

All the filters must be placed within `filters` key and the values should be an array of `object` containing:

- `fields` (String) <br />
Determine which field should be filtered
- `operator` (String) <br />
What kind of the operator to be used in the filter context
- `value` (String) <br />
The expected value

> The above schema when mapped to query string will be like this: <br />
> `?filters[0][field]=customer_name&filters[0][operator]=eq&filters[0][value]=asep`

## Supported Operators

- `eq`: Checks if the value is equal to the specified input.
- `neq`: Checks if the value is not equal to the specified input.
- `contains`: Checks if the value contains the specified input.
- `starts_with`: Checks if the value starts with the specified input.
- `ends_with`: Checks if the value ends with the specified input.
- `not_contains`: Checks if the value does not contain the specified input.
- `in` (array): Checks if the value is one of the specified inputs.
- `not_in` (array): Checks if the value is not any of the specified inputs.
- `have_all` (array): Checks if the value has all the specified inputs.
- `gt`: Checks if the value is greater than the specified input.
- `lt`: Checks if the value is less than the specified input.
- `gte`: Checks if the value is greater than or equal to the specified input.
- `lte`: Checks if the value is less than or equal to the specified input.

## Limitations

Since the `value` currently only supports the `string` type, all other types, including `array` and `number`,
need to be cast. This can lead to the possibility of inaccurate results. For instance, 
when casting the number type, Laravel Filterable goes through these steps:

- It checks whether the assigned `field` type is number based on `$rows` or `getRows()`.
- If this is true, it then checks if the `value` contains a prefix of `.`.
- If the prefix is present, it casts it as a `float`.
- If not, it casts it as an `int`.

While the `number` type might be secure due to the definition defined in `$rows` or `getRows()`,
`array` casting happens automatically. Here's how Filterable handles the casting to `array`:

- It checks if the operator is within `in`, `not_in`, or `have_all`.
- If this is true, it then checks if the `value` contains a prefix of `,`.
- If the prefix is present, it splits the `value` based on `,`.
- If not, it returns an array with the `value` wrapped (`[$value]`).

> Therefore, whenever you use the `in`, `not_in`, or `have_all` operator, you need to ensure that a `,`
> is not used as a prefix.

The implementation of custom operators or filters is not currently possible at this time.

Lastly, in regard to relationships, while it's not currently possible, I do have plans to add this feature in the future.


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [albetnov](https://github.com/albetnov)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
