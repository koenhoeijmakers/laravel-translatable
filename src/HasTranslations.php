<?php

namespace KoenHoeijmakers\LaravelTranslatable;

use Illuminate\Support\Arr;
use KoenHoeijmakers\LaravelTranslatable\Contracts\Services\TranslationSavingServiceContract;
use KoenHoeijmakers\LaravelTranslatable\Exceptions\MissingTranslationsException;
use KoenHoeijmakers\LaravelTranslatable\Scopes\JoinTranslationScope;

/**
 * Trait Translatable
 *
 * @package KoenHoeijmakers\LaravelTranslatable
 * @mixin \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
 */
trait HasTranslations
{
    /**
     * Boot the translatable trait.
     *
     * @return void
     */
    public static function bootHasTranslations()
    {
        if (config('translatable.use_saving_service', true)) {
            static::saving(function (self $self) {
                app()->make(TranslationSavingServiceContract::class)->rememberTranslationForModel($self);
            });

            static::saved(function (self $self) {
                app(TranslationSavingServiceContract::class)->storeTranslationOnModel($self);
            });
        }

        static::addGlobalScope(new JoinTranslationScope());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany($this->getTranslationModel(), $this->getTranslationForeignKey());
    }

    /**
     * Get the translation model.
     *
     * @return string
     */
    public function getTranslationModel()
    {
        if (isset($this->translationModel)) {
            return $this->translationModel;
        }

        return get_class($this) . $this->getTranslationModelSuffix();
    }

    /**
     * Get the translation model suffix.
     *
     * @return string
     */
    protected function getTranslationModelSuffix()
    {
        return 'Translation';
    }

    /**
     * Get the translation table.
     *
     * @return string
     */
    public function getTranslationTable()
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
        if (isset($this->translationForeignKey)) {
            return $this->translationForeignKey;
        }

        return $this->getForeignKey();
    }

    /**
     * Get the translatable.
     *
     * @return array
     * @throws \KoenHoeijmakers\LaravelTranslatable\Exceptions\MissingTranslationsException
     */
    public function getTranslatable()
    {
        if (!isset($this->translatable)) {
            throw new MissingTranslationsException('Model "' . static::class . '" is missing translations');
        }

        return $this->translatable;
    }

    /**
     * Get the translatable attributes.
     *
     * @return array
     */
    public function getTranslatableAttributes()
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
        return $this->translations()->updateOrCreate([$this->getLocaleKeyName(), $locale], $attributes);
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
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getTranslation(string $locale)
    {
        return $this->translations()->where($this->getLocaleKeyName(), $locale)->first();
    }

    /**
     * The locale key name.
     *
     * @return string
     */
    protected function getLocaleKeyName()
    {
        return config('translatable.locale_key_name', 'locale');
    }
}
