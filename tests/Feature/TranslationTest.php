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

        $this->assertDatabaseHas('test_models', ['id' => $model->getKey()]);
        $this->assertTrue($model->translationExists('nl'));
        $this->assertTrue($model->translationExists('de'));
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

        $model->translate('nl');

        $this->assertEquals($model->getLocale(), 'nl');
        $this->assertEquals($model->getAttribute('name'), 'Aap');
    }

    public function testCanRetrieveADifferentTranslationAndItsUpdatedInThatLocale()
    {
        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $model->storeTranslation('nl', ['name' => 'Aap']);

        $this->assertTrue($model->translationExists('nl'));

        $model->translate('nl');

        $this->assertEquals($model->getLocale(), 'nl');
        $this->assertEquals($model->getAttribute('name'), 'Aap');

        $model->update(['name' => 'Gorilla']);

        $this->assertEquals($model->getAttribute('name'), 'Gorilla');
        $this->assertEquals(TestModel::query()->find(1)->getAttribute('name'), 'Monkey');
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
