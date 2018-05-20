<?php

namespace KoenHoeijmakers\LaravelTranslatable;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use KoenHoeijmakers\LaravelTranslatable\Contracts\Services\TranslationSavingServiceContract;
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
        $this->app->singleton(TranslationSavingServiceContract::class, function (Application $app) {
            return new TranslationSavingService($app);
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/translatable.php', 'translatable');
    }
}
