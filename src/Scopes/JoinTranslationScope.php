<?php

namespace KoenHoeijmakers\LaravelTranslatable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\JoinClause;

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
        $builder->join($model->getTranslationTable(), function (JoinClause $join) use ($model) {
            $join->on(
                $model->getTable() . '.' . $model->getKeyName(),
                $model->getTranslationTable() . '.' . $model->getForeignKey()
            )->where($model->getLocaleKeyName(), $model->getLocale());
        })->select($model->getTable() . '.*', $this->formatTranslatableColumns($model));
    }

    /**
     * Format the translated columns.
     *
     * @param \Illuminate\Database\Eloquent\Model|\KoenHoeijmakers\LaravelTranslatable\HasTranslations $model
     * @return string
     */
    protected function formatTranslatableColumns(Model $model): string
    {
        $table = $model->getTranslationTable();

        return implode(',', array_map(function ($item) use ($table) {
            return $table . '.' . $item;
        }, $model->getTranslatable()));
    }
}
