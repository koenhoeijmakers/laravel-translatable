<?php

namespace KoenHoeijmakers\LaravelTranslatable\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use KoenHoeijmakers\LaravelTranslatable\TranslatableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [TranslatableServiceProvider::class];
    }

    protected function setUpDatabase()
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create('test_model_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('test_model_id');
            $table->string('locale');
            $table->string('name');
            $table->timestamps();

            $table->unique(['locale', 'test_model_id']);
            $table->foreign('test_model_id')->references('id')->on('test_models');
        });
    }
}
