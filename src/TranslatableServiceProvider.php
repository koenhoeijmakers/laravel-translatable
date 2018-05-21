<?php

namespace KoenHoeijmakers\LaravelTranslatable;

use Illuminate\Support\ServiceProvider;
use KoenHoeijmakers\LaravelTranslatable\Services\TranslationSavingService;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * Boots the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . ' /../config/translatable.php' => config_path('translatable.php'),
        ], 'config');
    }

    /**
     * Registers the package's services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TranslationSavingService::class, function () {
            return new TranslationSavingService();
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/translatable.php', 'translatable');
    }
}
