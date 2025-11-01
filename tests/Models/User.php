<?php

namespace Sharifuddin\LaravelSmartFilter\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sharifuddin\LaravelSmartFilter\Contracts\Filterable;
use Sharifuddin\LaravelSmartFilter\Traits\SmartFilter;

class User extends Model implements Filterable
{
    use HasFactory, SmartFilter;

    protected $fillable = ['name', 'email', 'age', 'is_active', 'salary'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function filterableFields(): array
    {
        return [
            'name' => ['operator' => 'like', 'type' => 'string'],
            'email' => ['operator' => 'like', 'type' => 'string'],
            'age' => ['operator' => '=', 'type' => 'integer'],
            'is_active' => ['operator' => '=', 'type' => 'boolean'],
            'salary' => ['operator' => 'between', 'type' => 'float'],
        ];
    }

    public function filterableRelations(): array
    {
        return [
            'posts' => [
                'fields' => ['title', 'content'],
                'max_depth' => 1,
            ],
        ];
    }

    public function filterConfig(): array
    {
        return [
            'strict_mode' => true,
        ];
    }
}
