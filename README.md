# Laravel Translatable
[![Packagist](https://img.shields.io/packagist/v/koenhoeijmakers/laravel-translatable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-translatable)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-translatable/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-translatable/?branch=master)
[![license](https://img.shields.io/github/license/koenhoeijmakers/laravel-translatable.svg?colorB=brightgreen)](https://github.com/koenhoeijmakers/laravel-translatable)
[![Packagist](https://img.shields.io/packagist/dt/koenhoeijmakers/laravel-translatable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-translatable)

A fresh new way to handle model translations.

# About
What makes this package so special is the way it handles the translations, 
how it retrieves them, how it stores them, and how it queries them.

### Querying
Due to how the package handles the translations, querying is a piece of cake, 
while for other packages you would have a `->whereTranslation('nl', 'column', '=', 'foo')` method.

But in this package you can just do `->where('column', '=', 'foo')` and it'll know what to query, just query how you used to!

### Retrieving
When you retrieve a model from the database, 
the package will join the translation table with the translation of the current locale `config/app.php`.

This makes it so that any translated column acts like it is "native" to the model, 
due to this we don't have to override a lot of methods on the model which is a big plus.

Need the model in a different language? call `$model->translate('nl')` and you're done, now want to save the `nl` translation? just call `->update()`, 
the model knows in which locale it is loaded, and it'll handle it accordingly.

```php
$animal = Animal::find(1);

$animal->translate('nl')->update(['name' => 'Aap']);
```

### Storing
You'll store your translations as if they're attributes on the model, so this will work like a charm:
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
### Setting up a model.
Start off by creating a migration and a model,
we'll go with the `Animal` model and the corresponding `AnimalTranslation` model.

#### Migrations
```php
Schema::create('animals', function (Blueprint $table) {
    $table->increments('id');
    $table->timestamps();
});
```

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

As you might see, the `Animal` model has made the `name` column translatable, but also fillable.
**This is needed to allow the model to save the translations properly**, in the end, what you can't set, you can't get.

> So make sure the `$translatable` columns are also `$fillable`
