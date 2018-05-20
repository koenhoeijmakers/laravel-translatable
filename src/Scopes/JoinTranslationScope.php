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
            ->where($model->getLocaleKeyName(), app()->getLocale())
            ->select($model->getTable() . '.*', $this->formatTranslatableColumns($model));
    }

    /**
     * Format the translated columns.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string
     */
    protected function formatTranslatableColumns(Model $model)
    {
        return implode(',', array_map(function ($item) use ($model) {
            return $model->getTranslationTable() . '.' . $item;
        }, $model->getTranslatable()));
    }
}
