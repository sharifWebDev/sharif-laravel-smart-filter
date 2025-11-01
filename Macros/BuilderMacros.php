<?php

namespace Sharifuddin\LaravelSmartFilter\Macros;

use Illuminate\Database\Eloquent\Builder;
use Sharifuddin\LaravelSmartFilter\Facades\SmartFilter;

class BuilderMacros
{
    /**
     * Register query builder macros.
     */
    public static function register(): void
    {
        Builder::macro('smartFilter', function (
            array $filters = [],
            array $options = []
        ) {
            /** @var Builder $this */
            return SmartFilter::apply(static::class, $filters, $options);
        });

        Builder::macro('filter', function (
            array $filters = [],
            array $options = []
        ) {
            return SmartFilter::smartFilter($filters, $options);
        });

        Builder::macro('filterFromRequest', function (
            array $allowedFilters = [],
            array $options = []
        ) {
            /** @var Builder $this */
            $filters = SmartFilter::parseFromRequest($allowedFilters);

            return SmartFilter::smartFilter($filters, $options);
        });
    }
}
