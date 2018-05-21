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

... and optionally publish the config.
```sh
php artisan vendor:publish --provider="KoenHoeijmakers\LaravelTranslatable\TranslatableServiceProvider"
```

# Usage
> Read this thoroughly.

## Creating a migration
Start off by creating a migration for the model you wish to translate, we'll go with the `Type` model,
and thus we'll call our table `type_translations`, which has a `belongsTo` of the `Type` model (`type_id`), 
a locale and our translatable columns, in our case `name`.

```php
Schema::create('type_translations', function (Blueprint $table) {
    $table->increments('id');
    $table->unsignedInteger('type_id');
    $table->string('locale');
    $table->string('name');
    $table->timestamps();
});
```

Of course create a model for this migration too, and make the translatable columns fillable.

```php
use Illuminate\Database\Eloquent\Model;

class TypeTranslation extends Model
{
    protected $fillable = ['name'];
}
```

## Registering the trait
Now you can register the trait, set the `$translatable` property and **make sure the translated columns are fillable**, 
this is important as the saving service gets the translatable columns from the "original" model,
and what you can't set, you can't get.

> Do not worry, it will never attempt to save the translatable columns on the "original" model.

```php
use Illuminate\Database\Eloquent\Model;
use KoenHoeijmakers\LaravelTranslatable\HasTranslations;

class Type extends Model
{
    use HasTranslations;
    
    protected $translatable = ['name'];
    
    protected $fillable = ['name'];
}
```

## Storing translations
The trait by defaults stores (and retrieves) the locale that has been set in the application (`app()->getLocale()`).

When you're saving one of the translatable models, 
it will pull and remember the translatable attributes from the original model,
thus you're able to fill the attribute on the original model, 
and it will still be saved in the translation table.

This works for the locale the application is ran in by default, 
but if the model has been translated with `->translate($locale)` it will save the model to the locale it was translated to.

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
