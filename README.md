# Laravel Translatable
[![Packagist](https://img.shields.io/packagist/v/koenhoeijmakers/laravel-translatable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-translatable)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-translatable/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-translatable/?branch=master)
[![license](https://img.shields.io/github/license/koenhoeijmakers/laravel-translatable.svg?colorB=brightgreen)](https://github.com/koenhoeijmakers/laravel-translatable)
[![Packagist](https://img.shields.io/packagist/dt/koenhoeijmakers/laravel-translatable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-translatable)

A fresh new way to handle model translations.

# Installation

Require the package.
```sh
composer require koenhoeijmakers/laravel-translatable
```

Publish the config.
```sh
php artisan vendor:publish --provider="KoenHoeijmakers\LaravelTranslatable\TranslatableServiceProvider"
```

# Usage
The trait by defaults stores (and retrieves) the locale that has been set in the application.

When you're saving one of the translatable models, it will pull and remember the translatable attributes from the original model,
thus you're able to fill the attribute on the original model, and it will still be saved in the translation table.

This works for the locale the application is ran in by default.

Of course you can save other translations in the same request by calling the `storeTranslation` or `storeTranslations` method.

```php
$model->storeTranslation('nl', ['foo' => 'bar']);

$model->storeTranslations([
    'nl' => [
        'foo' => 'bar',
    ],
    'de' => [
        'foo' => 'foo',
    ],
]);
```
