<?php

namespace Sharifuddin\LaravelSmartFilter;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Traits\Macroable;
use Sharifuddin\LaravelSmartFilter\Exceptions\SmartFilterException;

class SmartFilterManager
{
    use Macroable;

    public function __construct(
        protected Application $app
    ) {}

    /**
     * Apply filters to query builder.
     */
    public function apply(Builder $query, array $filters = [], array $options = []): Builder
    {
        $model = $query->getModel();

        if (!in_array(\Sharifuddin\LaravelSmartFilter\Contracts\Filterable::class, class_implements($model))) {
            throw new SmartFilterException(
                "Model [" . get_class($model) . "] must implement Filterable contract."
            );
        }

        return $model->applySmartFilters($query, $filters, $options);
    }

    /**
     * Parse filter parameters from request.
     */
    public function parseFromRequest(array $allowedFilters = []): array
    {
        $request = $this->app['request'];
        $filters = [];

        foreach ($allowedFilters as $field => $config) {
            $value = $request->input($field);

            if ($value !== null && $value !== '') {
                $filters[$field] = [
                    'value' => $this->processRequestValue($value, $config['type'] ?? 'string'),
                    'operator' => $config['operator'] ?? '=',
                    'type' => $config['type'] ?? 'string'
                ];
            }
        }

        return $filters;
    }

    /**
     * Process request value based on type.
     */
    protected function processRequestValue($value, string $type)
    {
        return match ($type) {
            'integer', 'number' => (int) $value,
            'float', 'decimal' => (float) $value,
            'boolean' => $this->processBooleanValue($value),
            'array' => is_array($value) ? $value : explode(',', $value),
            default => $value
        };
    }

    /**
     * Process boolean value from request.
     */
    protected function processBooleanValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $value = strtolower($value);
            return in_array($value, ['1', 'true', 'yes', 'on']);
        }

        return (bool) $value;
    }

    /**
     * Get filter configuration.
     */
    public function config(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return config('smart-filter');
        }

        return config("smart-filter.{$key}", $default);
    }

    /**
     * Check if filtering is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->config('enabled', true);
    }

    /**
     * Dynamically handle calls to the manager.
     */
    public function __call(string $method, array $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        throw new SmartFilterException("Method [{$method}] does not exist.");
    }
}
