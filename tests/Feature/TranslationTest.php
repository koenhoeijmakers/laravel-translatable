<?php

namespace KoenHoeijmakers\LaravelTranslatable\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use KoenHoeijmakers\LaravelTranslatable\HasTranslations;
use KoenHoeijmakers\LaravelTranslatable\Tests\TestCase;

class TranslationTest extends TestCase
{
    use RefreshDatabase;

    public function testTranslationsAreBeingSaved()
    {
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $this->assertTrue($model->getAttribute('name') === 'Monkey');
        $this->assertDatabaseHas('test_models', ['id' => $model->getKey()]);
        $this->assertDatabaseHas('test_model_translations', ['test_model_id' => $model->getKey(), 'name' => 'Monkey']);
    }

    public function testTranslationsCanBeSavedViaStoreTranslation()
    {
        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $model->storeTranslation('nl', ['name' => 'Aap']);

        $this->assertDatabaseHas('test_models', ['id' => $model->getKey()]);
        $this->assertDatabaseHas('test_model_translations', ['test_model_id' => $model->getKey(), 'name' => 'Aap']);
    }

    public function testTranslationsCanBeSavedViaStoreTranslationMethod()
    {
        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $model->storeTranslation('nl', ['name' => 'Aap']);

        $this->assertDatabaseHas('test_models', ['id' => $model->getKey()]);
        $this->assertTrue($model->translationExists('nl'));
    }

    public function testTranslationsCanBeSavedViaStoreTranslationsMethod()
    {
        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $model->storeTranslations([
            'nl' => ['name' => 'Aap'],
            'de' => ['name' => 'Affe'],
        ]);

        $this->assertTrue($model->translationExists('nl'));
        $this->assertTrue($model->translationExists('de'));

        $this->assertDatabaseHas('test_models', ['id' => $model->getKey()]);
    }

    public function testCanRetrieveADifferentTranslation()
    {
        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $model->storeTranslation('nl', ['name' => 'Aap']);

        $this->assertTrue($model->translationExists('nl'));

        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->find(1);

        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $translatedModel */
        $translatedModel = $model->translate('nl');

        $this->assertEquals($translatedModel->getLocale(), 'nl');
        $this->assertEquals($translatedModel->getAttribute('name'), 'Aap');
    }
}

class TestModel extends Model
{
    use HasTranslations;

    protected $fillable = ['name'];

    protected $translatable = ['name'];
}

class TestModelTranslation extends Model
{
    protected $fillable = ['name'];
}
