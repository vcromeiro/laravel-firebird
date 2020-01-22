# Firebird for Laravel

[![Latest Stable Version](https://poser.pugx.org/harrygulliford/laravel-firebird/v/stable)](https://packagist.org/packages/harrygulliford/laravel-firebird)
[![Total Downloads](https://poser.pugx.org/harrygulliford/laravel-firebird/downloads)](https://packagist.org/packages/harrygulliford/laravel-firebird)
[![License](https://poser.pugx.org/harrygulliford/laravel-firebird/license)](https://packagist.org/packages/harrygulliford/laravel-firebird)

This package adds support for the Firebird PDO driver in Laravel applications. Support for Laravel 5.5+ (including 6.x) with PHP 7.1+ and Firebird 2.5

## Installation

You can install the package via composer:

```json
composer require harrygulliford/laravel-firebird
```

_The package will automatically register itself._

Declare the connection within your `config/database.php` file, using `firebird` as the
driver:
```php
'connections' => [

    'firebird' => [
        'driver'   => 'firebird',
        'host'     => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', '/path_to/database.fdb'),
        'username' => env('DB_USERNAME', 'sysdba'),
        'password' => env('DB_PASSWORD', 'masterkey'),
        'charset'  => env('DB_CHARSET', 'UTF8'),
        'role'     => null,
    ],

],
```

## Credits
This package was originally forked from [acquestvanzuydam/laravel-firebird](https://github.com/jacquestvanzuydam/laravel-firebird) with enhancements from [sim1984/laravel-firebird](https://github.com/sim1984/laravel-firebird) and [harrygulliford/laravel-firebird](https://github.com/harrygulliford/laravel-firebird).

## License
Licensed under the [MIT](https://choosealicense.com/licenses/mit/) licence.
