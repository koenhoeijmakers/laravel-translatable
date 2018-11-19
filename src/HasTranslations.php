<?php

namespace KoenHoeijmakers\LaravelTranslatable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use KoenHoeijmakers\LaravelTranslatable\Exceptions\MissingTranslationsException;
use KoenHoeijmakers\LaravelTranslatable\Scopes\JoinTranslationScope;
use KoenHoeijmakers\LaravelTranslatable\Services\TranslationSavingService;

/**
 * Trait HasTranslations
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasTranslations
{
    /**
     * The current locale, used to handle internal states.
     *
     * @var string|null
     */
    protected $currentLocale = null;

    /**
     * Boot the translatable trait.
     *
     * @return void
     */
    public static function bootHasTranslations()
    {
        if (config('translatable.use_saving_service', true)) {
            static::saving(function (self $model) {
                app(TranslationSavingService::class)->rememberTranslationForModel($model);
            });

            static::saved(function (self $model) {
                app(TranslationSavingService::class)->storeTranslationOnModel($model);

                $model->refreshTranslation();
            });
        }

        static::deleting(function (self $model) {
            $model->purgeTranslations();
        });

        static::addGlobalScope(new JoinTranslationScope());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany($this->getTranslationModel(), $this->getTranslationForeignKey());
    }

    /**
     * Check if the translation by the given locale exists.
     *
     * @param string $locale
     * @return bool
     */
    public function translationExists(string $locale): bool
    {
        return $this->translations()->where($this->getLocaleKeyName(), $locale)->exists();
    }

    /**
     * Purge the translations.
     *
     * @return mixed
     */
    public function purgeTranslations()
    {
        return $this->translations()->delete();
    }

    /**
     * Get the translation model.
     *
     * @return string
     */
    public function getTranslationModel(): string
    {
        return property_exists($this, 'translationModel')
            ? $this->translationModel
            : get_class($this) . $this->getTranslationModelSuffix();
    }

    /**
     * Get the translation model suffix.
     *
     * @return string
     */
    protected function getTranslationModelSuffix(): string
    {
        return 'Translation';
    }

    /**
     * Get the translation table.
     *
     * @return string
     */
    public function getTranslationTable(): string
    {
        $model = $this->getTranslationModel();

        return (new $model())->getTable();
    }

    /**
     * Get the translation foreign key.
     *
     * @return string
     */
    public function getTranslationForeignKey()
    {
        return property_exists($this, 'translationForeignKey') ? $this->translationForeignKey : $this->getForeignKey();
    }

    /**
     * Get the translatable.
     *
     * @return array
     * @throws \KoenHoeijmakers\LaravelTranslatable\Exceptions\MissingTranslationsException
     */
    public function getTranslatable(): array
    {
        if (! isset($this->translatable)) {
            throw new MissingTranslationsException('Model "' . static::class . '" is missing translations');
        }

        return $this->translatable;
    }

    /**
     * Get the translatable attributes.
     *
     * @return array
     */
    public function getTranslatableAttributes(): array
    {
        return Arr::only($this->getAttributes(), $this->translatable);
    }

    /**
     * @param string $locale
     * @param array<string, mixed> $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function storeTranslation(string $locale, array $attributes = [])
    {
        if (! is_null($model = $this->translations()->where($this->getLocaleKeyName(), $locale)->first())) {
            $model->update($attributes);

            return $model;
        }

        $model = $this->translations()->make($attributes);
        $model->setAttribute($this->getLocaleKeyName(), $locale);
        $model->save();

        return $model;
    }

    /**
     * Store many translations at once.
     *
     * @param array<string, array> $translations
     * @return $this
     */
    public function storeTranslations(array $translations)
    {
        foreach ($translations as $locale => $translation) {
            $this->storeTranslation($locale, $translation);
        }

        return $this;
    }

    /**
     * @param string $locale
     * @return \Illuminate\Database\Eloquent\Model|self
     */
    public function getTranslation(string $locale)
    {
        return $this->translations()->where($this->getLocaleKeyName(), $locale)->first();
    }

    /**
     * @param string $locale
     * @param string $name
     * @return mixed
     */
    public function getTranslationValue(string $locale, string $name)
    {
        return $this->translations()->where($this->getLocaleKeyName(), $locale)->value($name);
    }

    /**
     * The locale key name.
     *
     * @return string
     */
    public function getLocaleKeyName(): string
    {
        return property_exists($this, 'localeKeyName') 
            ? $this->localeKeyName
            : config('translatable.locale_key_name', 'locale');
    }

    /**
     * Get the locale.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return null !== $this->currentLocale
            ? $this->currentLocale 
            : app()->getLocale();
    }

    /**
     * Refresh the translation (in the current locale).
     *
     * @return \Illuminate\Database\Eloquent\Model|null|HasTranslations
     */
    public function refreshTranslation()
    {
        if (! $this->exists) {
            return null;
        }

        $attributes = Arr::only(
            static::findOrFail($this->getKey())->attributes, $this->getTranslatable()
        );

        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        $this->syncOriginal();

        return $this;
    }

    /**
     * Translate the model to the given locale.
     *
     * @param string $locale
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function translate(string $locale)
    {
        if (! $this->exists) {
            return null;
        }

        $this->currentLocale = $locale;

        return $this->refreshTranslation();
    }

    /**
     * Format the translated columns.
     *
     * @return array
     */
    public function formatTranslatableColumnsForSelect(): array
    {
        $table = $this->getTranslationTable();

        return array_map(function ($item) use ($table) {
            return $table . '.' . $item;
        }, $this->getTranslatable());
    }

    /**
     * Get a new query builder that doesn't have any global scopes (except the JoinTranslationScope).
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQueryWithoutScopes(): Builder
    {
        return parent::newQueryWithoutScopes()
            ->withGlobalScope(JoinTranslationScope::class, new JoinTranslationScope());
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value)
    {
        return $this->where($this->getTable() . '.' . $this->getRouteKeyName(), $value)->first();
    }
}
