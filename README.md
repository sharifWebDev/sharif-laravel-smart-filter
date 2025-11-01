<p align="center">
<img src="https://via.placeholder.com/1500x500/3B82F6/FFFFFF?text=Laravel+Smart+Filter" alt="Laravel Smart Filter" width="100%">
</p>

<p align="center">
<a href="https://packagist.org/packages/sharifuddin/laravel-smart-filter"><img src="https://img.shields.io/packagist/v/sharifuddin/laravel-smart-filter" alt="Latest Version"></a>
<a href="https://packagist.org/packages/sharifuddin/laravel-smart-filter"><img src="https://img.shields.io/packagist/dt/sharifuddin/laravel-smart-filter" alt="Total Downloads"></a>
<a href="https://github.com/sharifuddin/laravel-smart-filter/actions"><img src="https://img.shields.io/github/actions/workflow/status/sharifuddin/laravel-smart-filter/tests.yml" alt="Build Status"></a>
<a href="https://packagist.org/packages/sharifuddin/laravel-smart-filter"><img src="https://img.shields.io/packagist/l/sharifuddin/laravel-smart-filter" alt="License"></a>
<a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4" alt="PHP Version"></a>
<a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-10.x%2B-FF2D20" alt="Laravel Version"></a>
</p>

---

## 🚀 Introduction

**Laravel Smart Filter** is a powerful and flexible filtering engine for Eloquent models.  
It enables dynamic, request-driven filtering across columns and relationships — with support for multiple operators, data types, and nested relations.

### ✨ Features

- ⚡ **Dynamic Filtering** — Filter data using arrays or request parameters
- 🔍 **Relation Filtering** — Automatically filters related models
- 🧩 **Multiple Operators** — `=`, `!=`, `>`, `<`, `like`, `in`, `between`, `null`, etc.
- ⚙️ **Type-Aware Parsing** — Handles string, numeric, boolean, and date types
- 🔄 **Request Integration** — Parse filters directly from HTTP requests
- 🧪 **Fully Tested & Configurable** — Comprehensive test coverage and flexible config

---

## 📦 Installation

### Requirements

- PHP 8.1 or higher
- Laravel 10.x, 11.x, or 12.x

### Install via Composer

```bash
composer require sharifuddin/laravel-smart-filter
```

### (Optional) Publish Configuration

```bash
php artisan vendor:publish --provider="Sharifuddin\\LaravelSmartFilter\\SmartFilterServiceProvider" --tag="smart-filter-config"
```

---

## 🎯 Quick Start

### 1️⃣ Add the Trait to a Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sharifuddin\LaravelSmartFilter\Traits\SmartFilter;

class Product extends Model
{
    use SmartFilter;

    public function getFilterableFields(): array
    {
        return [
            'name' => ['operator' => 'like', 'type' => 'string'],
            'price' => ['operator' => 'between', 'type' => 'float'],
            'category_id' => ['operator' => 'in', 'type' => 'integer'],
            'is_active' => ['operator' => '=', 'type' => 'boolean'],
            'created_at' => ['operator' => 'date', 'type' => 'date'],
        ];
    }

    public function getFilterableRelations(): array
    {
        return [
            'category' => [
                'fields' => ['name', 'slug'],
                'max_depth' => 1,
            ],
            'brand' => [
                'fields' => ['name', 'description'],
                'max_depth' => 1,
            ],
        ];
    }
}
```

---

## 🧩 Usage Examples

### 🔹 Basic Filtering

```php
$filters = [
    'name' => ['value' => 'Laptop', 'operator' => 'like', 'type' => 'string'],
    'price' => ['value' => [100, 1000], 'operator' => 'between', 'type' => 'float'],
    'is_active' => ['value' => true, 'operator' => '=', 'type' => 'boolean'],
];

$products = Product::applySmartFilters($filters)->get();
```

---

### 🔹 Filter from HTTP Request

```php
// Example URL: /products?name=Laptop&price_min=100&price_max=1000&category_id=1,2,3

$products = Product::filterFromRequest()->paginate(20);
```

Or specify allowed filters:

```php
$products = Product::filterFromRequest([
    'name' => ['operator' => 'like', 'type' => 'string'],
    'price' => ['operator' => 'between', 'type' => 'float'],
    'category_id' => ['operator' => 'in', 'type' => 'integer'],
])->get();
```

---

### 🔹 Relation Filtering

```php
$filters = [
    'category.name' => ['value' => 'Electronics', 'operator' => 'like', 'type' => 'string'],
    'brand.name' => ['value' => 'Apple', 'operator' => 'like', 'type' => 'string'],
];

$products = Product::applySmartFilters($filters)->get();
```

---

### 🔹 Combined Example (Controller)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::filterFromRequest([
                'name' => ['operator' => 'like', 'type' => 'string'],
                'price' => ['operator' => 'between', 'type' => 'float'],
                'category_id' => ['operator' => 'in', 'type' => 'integer'],
                'is_active' => ['operator' => '=', 'type' => 'boolean'],
            ])
            ->with(['category', 'brand'])
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return view('products.index', compact('products'));
    }
}
```

---

## ⚙️ Configuration

**`config/smart-filter.php`**

```php
return [
    'defaults' => [
        'deep' => true,
        'max_relation_depth' => 2,
        'case_sensitive' => false,
        'strict_mode' => false,
    ],

    'fields' => [
        'excluded' => ['id', 'created_at', 'updated_at', 'deleted_at', 'password'],
        'default_operators' => [
            'string' => 'like',
            'integer' => '=',
            'float' => '=',
            'boolean' => '=',
            'date' => '=',
            'array' => 'in',
        ],
    ],

    'request' => [
        'prefix' => '',
        'array_delimiter' => ',',
        'date_format' => 'Y-m-d',
    ],

    'performance' => [
        'max_join_tables' => 5,
        'query_timeout' => 30,
        'max_filters' => 20,
    ],
];
```

---

## 🧪 Testing

```bash
# Run all tests
composer test

# With coverage
composer test-coverage
```

Example test:

```php
public function test_filters_products_by_price_and_status()
{
    $filters = [
        'price' => ['value' => [100, 500], 'operator' => 'between', 'type' => 'float'],
        'is_active' => ['value' => true, 'operator' => '=', 'type' => 'boolean'],
    ];

    $products = Product::applySmartFilters($filters)->get();

    $this->assertTrue($products->every(fn($p) => $p->is_active));
}
```

---

## 🧠 API Reference

### ➤ `scopeApplySmartFilters()`

```php
public function scopeApplySmartFilters(
    Builder $query,
    array $filters = [],
    array $options = []
): Builder
```

### ➤ `scopeFilterFromRequest()`

```php
public function scopeFilterFromRequest(
    Builder $query,
    array $allowedFilters = [],
    array $options = []
): Builder
```

---

## 🧰 Performance Tips

- Use indexed columns for frequent filters
- Prefer exact (`=`) over `LIKE` for faster queries
- Limit filters using config: `max_filters`
- Always paginate large results

---

## 🤝 Contributing

Contributions are welcome!  
See [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## 📄 License

This package is open-sourced software licensed under the **MIT License**.

---

<p align="center">
<strong>Laravel Smart Filter</strong> — Powerful, Relation-Aware Filtering for Eloquent. ⚡
</p>

<p align="center">
<a href="https://github.com/sharifuddin/laravel-smart-filter">GitHub</a> •
<a href="https://packagist.org/packages/sharifuddin/laravel-smart-filter">Packagist</a> •
<a href="https://github.com/sharifuddin/laravel-smart-filter/issues">Issues</a> •
<a href="https://github.com/sharifuddin/laravel-smart-filter/discussions">Discussions</a>
</p>
