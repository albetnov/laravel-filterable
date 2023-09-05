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

## Usage

Simply add `Filterable` trait in your model and define either `$filterableColumns` or `filterableColumns()` 
(if you need extra logic) to define a filterable columns:

```php
<?php

namespace App\Models;

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model {
    use Filterable;
    
    protected arrray $filterableColumns = [
        'ticket_no' => FilterableType::NUMBER,
        'customer_name' => FilterableType::TEXT,
        'schedule' => FilterableType::DATE
    ];
    
    protected function filterableColumns(): array {
        return [
            'customer_address' => FilterableType::custom(),
        ];
    }
}
```

### Getting Filterable Columns

If both are defined, Filterable will priority the method over property. As defined in:
[Filterable.php](https://github.com/albetnov/laravel-filterable/blob/99c460686339355c59d693a8572e0d2106d1eed5/src/Traits/Filterable.php#L20-L31)

If none exist though, Filterable will throws `PropertyNotExist` exception.

### Filterable Type

There are Five FilterableType options available:

- Number <br />
This type will cast the given payload to either float or int, depending on whether the string contains a . prefix (indicating a float) or not (indicating an int).

- Text <br />
A type designed for handling text-based filters.

- Date <br />
A type intended for handling date-based filters. This will cast the payload to the Carbon format and adjust the query accordingly.

- Boolean <br />
A type intended for handling boolean-based filters. This type will cast the payload to boolean depending on `0` and `1`.

### Modifiers

Each of the `FilterableType` supports a modifier to alter the behaviour of filtering from the assigned field.
There are 2 modifiers you can use for now.

- `limit` <br />
Allows you to limit the available operator, leaving the rest of the operators become invalid and throws `OperatorNotExist`
exception. The function have one argument receiving an array of `Operators`. Usage Example:

```php

use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Enums\Operators;

protected function filterableColumns(): array {
    return [
        'customer_name' => FilterableType::TEXT->limit([Operators::CONTAINS, Operators::NOT_CONTAINS, 
        Operators::STARTS_WITH, Operators::ENDS_WITH])
    ];
}
```

- `related` <br />
Allows you to replace the query to use `whereHas` so that the filter apply to relation level. The function receives 2 
arguments, first is the relationship name, and second is the extra query condition which are optional. Usage Example:

```php

use Albet\LaravelFilterable\Enums\FilterableType;
use Illuminate\Database\Eloquent\Relations\HasOne;

protected function filterableColumns(): array {
    return [
        'flight_license' => FilterableType::NUMBER->related('flight', fn($query) => $query->where('status', 'A'))
    ];
}

public function flight(): HasOne {
    $this->hasOne(Flight::class);
}
```

> The modifiers can be chained together: `FilterableType::DATE->related()->limit()` to combine conditions

### Custom Type

> Custom Type does not support modifier.

As mentioned before there are 5 types exist for Filterable, the last one is `custom` which are treated differently. 
Custom Type is part of static method of `FilterableType` and therefore requires you to define it in `filterableColumns()`
method.

```php
use Albet\LaravelFilterable\Enums\FilterableType;

FilterableType::custom();
```

The custom method accept one argument, `$allowedOperators` that are an array of `Operators`. This argument used to define
the whitelist of allowed operators for your custom filter.

The custom type requires a handler, both of the handler and the field have to be defined under this convention:

```php
use Albet\LaravelFilterable\Enums\FilterableType;
use Albet\LaravelFilterable\Enums\Operators;
use Albet\LaravelFilterable\Operator;
use Albet\LaravelFilterable\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model {
    use Filterable;
    
    public function filterableColumns(): array{
        return [
            'customer_address' => FilterableType::custom([Operators::CONTAINS, Operators::NOT_CONTAINS])
        ];
    }
    
    public function filterCustomAddress(Builder $builder, string $operator, string $value): void {
        dump($operator); // 'contains' or 'not_contains' (raw string operator)
        dump($value); // raw string value
        $builder->whereHas('customer', fn($query) => $query->where('name', 'LIKE', "%$value%"));
    }
}
```

Notice that the columns are defined in snake case while the method is camel case. Your function should also have three
arguments defined. First is the `Builder`, a raw `operator`, and finally a raw `value`. The `raw` term means that these
value are not formatted and they are passed quickly from the query string. However, they are **validated**.

## Using in model

Just call filter scope and you're set:

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

The value is limited exclusively to the `string` type, and each casting is performed through types defined in 
`filterableColumns`. Ambiguous casting may occur for` arrays` with items containing `,` as the value delimiter. 
Please avoid using `,` in your values, as they are used as the internal array delimiter. Another case could involve 
`numbers` with more than one `.` delimiter.

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
