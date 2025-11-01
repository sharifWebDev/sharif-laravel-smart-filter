<?php

namespace Sharifuddin\LaravelSmartFilter\Exceptions;

use Exception;

class SmartFilterException extends Exception
{
    /**
     * Create a new exception for invalid model.
     */
    public static function invalidModel(string $modelClass): self
    {
        return new self(
            "Model [{$modelClass}] must implement filterable contract to use smart filter."
        );
    }

    /**
     * Create a new exception for invalid configuration.
     */
    public static function invalidConfiguration(string $key): self
    {
        return new self(
            "Invalid configuration key [{$key}] in smart-filter config."
        );
    }

    /**
     * Create a new exception for relation error.
     */
    public static function relationError(string $relation, string $message): self
    {
        return new self(
            "Error filtering relation [{$relation}]: {$message}"
        );
    }
}
