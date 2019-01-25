<?php

namespace KoenHoeijmakers\LaravelTranslatable\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use KoenHoeijmakers\LaravelTranslatable\Exceptions\MissingTranslationsException;
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

    public function testCanPurgeTranslations()
    {
        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $this->assertTrue($model->translationExists('en'));

        $model->purgeTranslations();

        $this->assertFalse($model->translationExists('en'));
    }

    public function testModelGetsDeletedAndTranslationsArePurged()
    {
        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $this->assertTrue($model->translationExists('en'));

        $model->delete();

        $this->assertFalse($model->translationExists('en'));
        $this->assertDatabaseMissing('test_models', [$model->getKeyName() => $model->getKey()]);
    }

    public function testCanGetTranslationModel()
    {
        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $this->assertInstanceOf(TestModelTranslation::class, $model->getTranslation('en'));
    }

    public function testCanGetTranslationValue()
    {
        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $this->assertEquals('Monkey', $model->getTranslationValue('en', 'name'));
    }

    public function testRefreshingTranslationsOnANonExistingModelReturnsNull()
    {
        $model = new TestModel();

        $this->assertNull($model->refreshTranslation());
    }

    public function testTranslatingANonExistingModelReturnsNull()
    {
        $model = new TestModel();

        $this->assertNull($model->translate('nl'));
    }

    public function testResolvingRouteBindingsReturnsCorrectModel()
    {
        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $this->assertTrue($model->is($model->resolveRouteBinding($model->getKey())));
    }

    public function testTranslationModelCanBeOverridden()
    {
        $model = new TestModelWithTranslationModelOverride();

        $this->assertSame($model->getTranslationTable(), 'test_model_translation_differents');
    }

    public function testModelIsMissingTranslations()
    {
        $model = new TestModelWithoutTranslations();

        $this->expectException(MissingTranslationsException::class);

        $model->getTranslatable();
    }

    public function testCanOverrideLocaleKey()
    {
        $model = new TestModelLocaleKey();

        $this->assertEquals('lang', $model->getLocaleKeyName());
    }

    public function testCanAddSelect()
    {
        /** @var \KoenHoeijmakers\LaravelTranslatable\Tests\Feature\TestModel $model */
        $model = TestModel::query()->create([
            'name' => 'Monkey',
        ]);

        $result = TestModel::query()->select('test_model_translations.id AS translation_id')->first();

        $this->assertEquals($model->getTranslation('en')->getKey(), $result->translation_id);
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

class TestModelWithTranslationModelOverride extends Model
{
    use HasTranslations;

    protected $table = 'test_models';

    protected $fillable = ['name'];

    protected $translatable = ['name'];

    protected $translationModel = TestModelTranslationDifferent::class;
}

class TestModelTranslationDifferent extends Model
{
    protected $fillable = ['name'];
}

class TestModelWithoutTranslations extends Model
{
    use HasTranslations;

    protected $fillable = ['name'];

    protected $table = 'test_models';
}

class TestModelLocaleKey extends Model
{
    use HasTranslations;

    protected $localeKeyName = 'lang';

    protected $fillable = ['name'];

    protected $table = 'test_models';
}
