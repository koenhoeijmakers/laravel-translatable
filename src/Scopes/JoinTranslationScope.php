<?php

namespace KoenHoeijmakers\LaravelTranslatable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class JoinTranslationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder                                                    $builder
     * @param  \Illuminate\Database\Eloquent\Model|\KoenHoeijmakers\LaravelTranslatable\HasTranslations $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->join($model->getTranslationTable(), $model->getTable() . '.' . $model->getKeyName(), $model->getTranslationTable() . '.' . $model->getForeignKey())
            ->where('locale', app()->getLocale())
            ->select($model->getTable() . '.*', $model->getTranslatable());
    }
}
