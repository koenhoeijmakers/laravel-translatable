# Laravel Translatable
[![Packagist](https://img.shields.io/packagist/v/koenhoeijmakers/laravel-translatable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-translatable)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-translatable/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-translatable/?branch=master)
[![license](https://img.shields.io/github/license/koenhoeijmakers/laravel-translatable.svg?colorB=brightgreen)](https://github.com/koenhoeijmakers/laravel-translatable)
[![Packagist](https://img.shields.io/packagist/dt/koenhoeijmakers/laravel-translatable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-translatable)

A fresh new way to handle Model translations, the translations are joined into the Model 
instead of making you query a relation or get every single attribute's translation one by one.

## Installation
Require the package.
```sh
composer require koenhoeijmakers/laravel-translatable
```

... and optionally publish the config.
```sh
php artisan vendor:publish --provider="KoenHoeijmakers\LaravelTranslatable\TranslatableServiceProvider"
```

## Usage
### Setting up a translatable Model.
Start off by creating a migration and a Model,
we'll go with the `Animal` Model and the corresponding `AnimalTranslation` Model.

#### Migrations
```php
Schema::create('animals', function (Blueprint $table) {
    $table->increments('id');
    $table->timestamps();
});
```

Always have a `locale` and a `foreign_key` to the original Model, in our case `animal_id`.

```php
Schema::create('animal_translations', function (Blueprint $table) {
    $table->increments('id');
    $table->unsignedInteger('animal_id');
    $table->string('locale');
    $table->string('name');
    $table->timestamps();
    
    $table->unique(['locale', 'animal_id']);
    $table->foreign('animal_id')->references('id')->on('animals');
});
```

#### Models
Register the trait on the Model, and add the columns that should be translted to the `$translatable` property,
**But also make them fillable**, this is because the saving is handled through events,
this way we don't have to change the `save` method and makes the package more interoperable.

> So make sure the `$translatable` columns are also `$fillable` on both Models.

```php
use Illuminate\Database\Eloquent\Model;
use KoenHoeijmakers\LaravelTranslatable\HasTranslations;

class Animal extends Model
{
    use HasTranslations;
    
    protected $translatable = ['name'];
    
    protected $fillable = ['name'];
}
```

```php
use Illuminate\Database\Eloquent\Model;

class AnimalTranslation extends Model
{
    protected $fillable = ['name'];
}
```

This is pretty much all there is to it, but you can read more about the package down here.

## About
What makes this package so special is the way it handles the translations, 
how it retrieves them, how it stores them, and how it queries them.

### Querying
Due to how the package handles the translations, querying is a piece of cake, 
while for other packages you would have a `->whereTranslation('nl', 'column', '=', 'foo')` method.

But in this package you can just do `->where('column', '=', 'foo')` and it'll know what to query, just query how you used to!

### Retrieving
When you retrieve a Model from the database, 
the package will join the translation table with the translation of the current locale `config/app.php`.

This makes it so that any translated column acts like it is "native" to the Model, 
due to this we don't have to override a lot of methods on the Model which is a big plus.

Need the Model in a different language? call `$Model->translate('nl')` and you're done, now want to save the `nl` translation? just call `->update()`, 
the Model knows in which locale it is loaded, and it'll handle it accordingly.

```php
$animal = Animal::find(1);

$animal->translate('nl')->update(['name' => 'Aap']);
```

### Storing
You'll store your translations as if they're attributes on the Model, so this will work like a charm:
```php
Animal::create(['name' => 'Ape']);
```

But you might want to store multiple translations in one request, so you could always call the `->storeTranslation()` or the `->storeTranslations()` method.

```php
$animal = Animal::create(['name' => 'Monkey']);

$animal->storeTranslation('nl', [
    'name' => 'Aap',
]);

$animal->storeTranslation([
    'nl' => [
        'name' => 'Aap',
    ],
    'de' => [
        'name' => 'Affe',
    ],
]);
```

## Available methods
Check if a translation of the given `$locale` exists.
```php
public function translationExists(string $locale): bool
```

Get all the translatable attributes.
```php
public function getTranslatable(): array
```

Get all the translatable attributes and their values.
```php
public function getTranslatableAttributes(): array
```

Store a single translation by the given `$locale`.
```php
public function storeTranslation(string $locale, array $attributes = [])
```

Store multiple translations at once (just a loop for `storeTranslation()`)
```php
public function storeTranslations(array $translations)
```

Get the translation for the given `$locale`.
```php
public function getTranslation(string $locale)
```

Refresh the translated attributes.
```php
public function refreshTranslation()
```

Translate the given Model (returns a new `Model` instance, but translated in the given `$locale`).
```php
public function translate(string $locale)
```
