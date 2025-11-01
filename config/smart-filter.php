<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Package Enabled
    |--------------------------------------------------------------------------
    |
    | This option determines if the smart filter functionality is enabled.
    | You can use this to disable filtering globally in certain environments.
    |
    */
    'enabled' => env('SMART_FILTER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Filter Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can define the default filter behavior for your application.
    | These settings can be overridden per model or per filter call.
    |
    */
    'defaults' => [
        'deep' => true,
        'max_relation_depth' => 2,
        'case_sensitive' => false,
        'strict_mode' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which fields should be included or excluded from filtering,
    | and define default operators for different field types.
    |
    */
    'fields' => [
        'excluded' => [
            'id',
            'uuid',
            'created_at',
            'updated_at',
            'deleted_at',
            'password',
            'remember_token',
            'email_verified_at',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ],

        'default_operators' => [
            'string' => 'like',
            'integer' => '=',
            'float' => '=',
            'boolean' => '=',
            'date' => '=',
            'array' => 'in',
        ],

        'types' => [
            'text' => ['string', 'text', 'varchar', 'char'],
            'numeric' => ['integer', 'bigint', 'smallint', 'tinyint', 'decimal', 'float', 'double'],
            'boolean' => ['boolean'],
            'date' => ['date', 'datetime', 'timestamp'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Relation Configuration
    |--------------------------------------------------------------------------
    |
    | Configure relation filter behavior including auto-discovery and
    | relation-specific settings.
    |
    */
    'relations' => [
        'auto_discover' => true,
        'max_depth' => 3,
        'excluded' => [
            'password',
            'secret',
            'tokens',
            'oauth_providers',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Optimize filter performance with these settings.
    |
    */
    'performance' => [
        'max_join_tables' => 5,
        'query_timeout' => 30,
        'max_filters' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how filters are parsed from HTTP requests.
    |
    */
    'request' => [
        'prefix' => '',
        'array_delimiter' => ',',
        'date_format' => 'Y-m-d',
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Configuration
    |--------------------------------------------------------------------------
    |
    | Enable debug mode for development.
    |
    */
    'debug' => [
        'enabled' => env('SMART_FILTER_DEBUG', false),
        'log_queries' => false,
        'log_performance' => false,
    ],
];
