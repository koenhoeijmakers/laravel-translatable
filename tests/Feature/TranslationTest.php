<?php

namespace KoenHoeijmakers\LaravelTranslatable\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use KoenHoeijmakers\LaravelTranslatable\HasTranslations;
use KoenHoeijmakers\LaravelTranslatable\Tests\TestCase;

class TranslationTest extends TestCase
{
    public function testTranslationsAreBeingSaved()
    {
        $model = TestModel::query()->create([
            'name' => 'Koen',
        ]);

        $this->assertTrue($model->getAttribute('name') === 'Koen');
        $this->assertDatabaseHas('test_models', ['id' => $model->getKey()]);
        $this->assertDatabaseHas('test_model_translations', ['test_model_id' => $model->getKey(), 'name' => 'Koen']);
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
