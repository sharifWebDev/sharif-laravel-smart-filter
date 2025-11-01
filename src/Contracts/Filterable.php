<?php

namespace Sharifuddin\LaravelSmartFilter\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Filterable
{
    /**
     * Apply smart filters to the query.
     */
    public function scopeApplySmartFilters(
        Builder $query,
        array $filters = [],
        array $options = []
    ): Builder;

    /**
     * Get filterable fields for the model.
     */
    public function getFilterableFields(): array;

    /**
     * Get filterable relations for the model.
     */
    public function getFilterableRelations(): array;

    /**
     * Get filter configuration for the model.
     */
    public function getFilterConfig(): array;
}
