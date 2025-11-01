<?php

namespace Sharifuddin\LaravelSmartFilter\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait SmartFilter
{
    /**
     * Apply smart filters to query.
     */
    public function scopeApplySmartFilters(
        Builder $query,
        array $filters = [],
        array $options = []
    ): Builder {
        if (! $this->shouldPerformFiltering($filters)) {
            return $query;
        }

        $config = $this->buildFilterConfig($options);
        $filters = $this->resolveFilters($filters);

        return $query->where(function ($q) use ($filters, $config) {
            $this->applyFilterConditions($q, $filters, $config);
        });
    }

    /**
     * Determine if filtering should be performed.
     */
    protected function shouldPerformFiltering(array $filters): bool
    {
        return ! empty($filters) && config('smart-filter.enabled', true);
    }

    /**
     * Build filter configuration.
     */
    protected function buildFilterConfig(array $options): array
    {
        $defaults = [
            'deep' => true,
            'max_relation_depth' => 2,
            'case_sensitive' => false,
            'strict_mode' => false,
        ];

        return array_merge(
            $defaults,
            config('smart-filter.defaults', []),
            $this->getFilterConfig(),
            $options
        );
    }

    /**
     * Resolve filters from various sources.
     */
    protected function resolveFilters(array $filters): array
    {
        if (! empty($filters)) {
            return $filters;
        }

        // Try to get filters from request
        $request = request();
        if (! $request) {
            return [];
        }

        $filterableFields = $this->getFilterableFields();
        $resolvedFilters = [];

        foreach ($filterableFields as $field => $config) {
            $value = $request->input($field);

            if ($value !== null && $value !== '') {
                $resolvedFilters[$field] = [
                    'value' => $value,
                    'operator' => $config['operator'] ?? '=',
                    'type' => $config['type'] ?? 'string',
                ];
            }
        }

        return $resolvedFilters;
    }

    /**
     * Apply filter conditions to query.
     */
    protected function applyFilterConditions($query, array $filters, array $config): void
    {
        foreach ($filters as $field => $filter) {
            $this->applySingleFilter($query, $field, $filter, $config);
        }
    }

    /**
     * Apply a single filter condition.
     */
    protected function applySingleFilter($query, string $field, array $filter, array $config): void
    {
        $value = $filter['value'] ?? null;
        $operator = $filter['operator'] ?? '=';
        $type = $filter['type'] ?? 'string';

        if ($value === null || $value === '') {
            return;
        }

        // Check if field is a relation
        if (Str::contains($field, '.') && $config['deep'] ?? true) {
            $this->applyRelationFilter($query, $field, $value, $operator, $type, $config);
        } else {
            $this->applyLocalFilter($query, $field, $value, $operator, $type, $config);
        }
    }

    /**
     * Apply local field filter.
     */
    protected function applyLocalFilter($query, string $field, $value, string $operator, string $type, array $config): void
    {
        $table = $this->getTable();

        // Validate field is filterable
        if (! $this->isFieldFilterable($field)) {
            return;
        }

        // Process value based on type
        $processedValue = $this->processFilterValue($value, $type, $operator);

        // Apply the filter
        $this->applyOperator($query, "{$table}.{$field}", $operator, $processedValue, $type);
    }

    /**
     * Apply relation filter.
     */
    protected function applyRelationFilter($query, string $field, $value, string $operator, string $type, array $config): void
    {
        $parts = explode('.', $field);
        $relationName = $parts[0];
        $relationField = $parts[1];

        // Check if relation is filterable
        if (! $this->isRelationFilterable($relationName)) {
            return;
        }

        $maxDepth = $config['max_relation_depth'] ?? 2;

        if ($maxDepth > 0) {
            $query->whereHas($relationName, function ($relQuery) use ($relationField, $value, $operator, $type, $config) {
                $relatedModel = $relQuery->getModel();

                if (method_exists($relatedModel, 'scopeApplySmartFilters')) {
                    $nestedConfig = array_merge($config, [
                        'max_relation_depth' => ($config['max_relation_depth'] ?? 2) - 1,
                    ]);

                    $relatedModel->applyLocalFilter($relQuery, $relationField, $value, $operator, $type, $nestedConfig);
                } else {
                    $this->applyFallbackRelationFilter($relQuery, $relationField, $value, $operator, $type);
                }
            });
        }
    }

    /**
     * Fallback relation filter implementation.
     */
    protected function applyFallbackRelationFilter($query, string $field, $value, string $operator, string $type): void
    {
        $table = $query->getModel()->getTable();
        $processedValue = $this->processFilterValue($value, $type, $operator);

        $this->applyOperator($query, "{$table}.{$field}", $operator, $processedValue, $type);
    }

    /**
     * Apply operator to query.
     */
    protected function applyOperator($query, string $field, string $operator, $value, string $type): void
    {
        switch ($operator) {
            case '=':
            case '!=':
            case '>':
            case '>=':
            case '<':
            case '<=':
                $query->where($field, $operator, $value);
                break;

            case 'like':
                $query->where($field, 'LIKE', $value);
                break;

            case 'not_like':
                $query->where($field, 'NOT LIKE', $value);
                break;

            case 'in':
                $query->whereIn($field, (array) $value);
                break;

            case 'not_in':
                $query->whereNotIn($field, (array) $value);
                break;

            case 'between':
                $query->whereBetween($field, (array) $value);
                break;

            case 'not_between':
                $query->whereNotBetween($field, (array) $value);
                break;

            case 'null':
                $query->whereNull($field);
                break;

            case 'not_null':
                $query->whereNotNull($field);
                break;

            case 'date':
                $query->whereDate($field, $value);
                break;

            case 'month':
                $query->whereMonth($field, $value);
                break;

            case 'year':
                $query->whereYear($field, $value);
                break;

            case 'day':
                $query->whereDay($field, $value);
                break;
        }
    }

    /**
     * Process filter value based on type and operator.
     */
    protected function processFilterValue($value, string $type, string $operator)
    {
        switch ($type) {
            case 'string':
                if ($operator === 'like') {
                    return "%{$value}%";
                }

                return $value;

            case 'integer':
            case 'number':
                return (int) $value;

            case 'float':
            case 'decimal':
                return (float) $value;

            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);

            case 'date':
                return is_string($value) ? $value : $value;

            case 'array':
                return is_array($value) ? $value : explode(',', $value);

            default:
                return $value;
        }
    }

    /**
     * Get filterable fields for the model.
     */
    public function getFilterableFields(): array
    {
        if (method_exists($this, 'filterableFields')) {
            $customFields = $this->filterableFields();
            if (is_array($customFields)) {
                return $customFields;
            }
        }

        return $this->getDefaultFilterableFields();
    }

    /**
     * Get default filterable fields.
     */
    protected function getDefaultFilterableFields(): array
    {
        $table = $this->getTable();
        $allColumns = Schema::getColumnListing($table);
        $excluded = config('smart-filter.fields.excluded', []);

        $filterableFields = [];

        foreach ($allColumns as $column) {
            if (! in_array($column, $excluded) && ! Str::endsWith($column, ['_token', 'password', 'secret'])) {
                $filterableFields[$column] = [
                    'type' => $this->getColumnType($table, $column),
                    'operator' => '=',
                ];
            }
        }

        return $filterableFields;
    }

    /**
     * Get column type.
     */
    protected function getColumnType(string $table, string $column): string
    {
        try {
            $type = Schema::getColumnType($table, $column);

            return match ($type) {
                'integer', 'bigint', 'smallint', 'tinyint' => 'integer',
                'decimal', 'float', 'double' => 'float',
                'boolean' => 'boolean',
                'date', 'datetime', 'timestamp' => 'date',
                default => 'string'
            };
        } catch (\Exception $e) {
            return 'string';
        }
    }

    /**
     * Get filterable relations for the model.
     */
    public function getFilterableRelations(): array
    {
        if (method_exists($this, 'filterableRelations')) {
            $customRelations = $this->filterableRelations();
            if (is_array($customRelations)) {
                return $customRelations;
            }
        }

        return $this->getDefaultFilterableRelations();
    }

    /**
     * Get default filterable relations.
     */
    protected function getDefaultFilterableRelations(): array
    {
        $relations = [];
        $columns = Schema::getColumnListing($this->getTable());

        foreach ($columns as $column) {
            if (Str::endsWith($column, '_id')) {
                $relationName = Str::camel(str_replace('_id', '', $column));

                if ($this->isValidRelation($relationName)) {
                    $relations[$relationName] = [
                        'fields' => $this->getRelationFilterableFields($relationName),
                        'max_depth' => 1,
                    ];
                }
            }
        }

        return $relations;
    }

    /**
     * Get filter configuration for the model.
     */
    public function getFilterConfig(): array
    {
        if (method_exists($this, 'filterConfig')) {
            $customConfig = $this->filterConfig();
            if (is_array($customConfig)) {
                return $customConfig;
            }
        }

        return [];
    }

    /**
     * Check if field is filterable.
     */
    protected function isFieldFilterable(string $field): bool
    {
        $filterableFields = $this->getFilterableFields();

        return array_key_exists($field, $filterableFields);
    }

    /**
     * Check if relation is filterable.
     */
    protected function isRelationFilterable(string $relationName): bool
    {
        $filterableRelations = $this->getFilterableRelations();

        return array_key_exists($relationName, $filterableRelations);
    }

    /**
     * Check if relation is valid.
     */
    protected function isValidRelation(string $relationName): bool
    {
        try {
            return method_exists($this, $relationName) &&
                is_object($this->{$relationName}()) &&
                method_exists($this->{$relationName}(), 'getRelated');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get filterable fields for a relation.
     */
    protected function getRelationFilterableFields(string $relationName): array
    {
        try {
            $relatedModel = $this->{$relationName}()->getRelated();

            if (method_exists($relatedModel, 'getFilterableFields')) {
                return $relatedModel->getFilterableFields();
            }

            return $this->getDefaultFilterableFieldsForModel($relatedModel);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get default filterable fields for a model.
     */
    protected function getDefaultFilterableFieldsForModel($model): array
    {
        $table = $model->getTable();
        $allColumns = Schema::getColumnListing($table);
        $excluded = config('smart-filter.fields.excluded', []);

        $fields = [];

        foreach ($allColumns as $column) {
            if (! in_array($column, $excluded) && ! Str::endsWith($column, ['_token', 'password', 'secret'])) {
                $fields[$column] = [
                    'type' => $this->getColumnType($table, $column),
                    'operator' => '=',
                ];
            }
        }

        return $fields;
    }
}
