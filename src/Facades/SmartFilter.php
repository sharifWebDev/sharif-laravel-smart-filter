<?php

namespace Sharifuddin\LaravelSmartFilter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Builder apply(\Illuminate\Database\Eloquent\Builder $query, array $filters = [], array $options = [])
 * @method static array parseFromRequest(array $allowedFilters = [])
 * @method static mixed config(string $key = null, mixed $default = null)
 * @method static bool isEnabled()
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 *
 * @see \Sharifuddin\LaravelSmartFilter\SmartFilterManager
 */
class SmartFilter extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'smart-filter';
    }
}
