<?php

namespace Sharifuddin\LaravelSmartFilter\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sharifuddin\LaravelSmartFilter\Contracts\Filterable;
use Sharifuddin\LaravelSmartFilter\Traits\SmartFilter;

class Post extends Model implements Filterable
{
    use HasFactory, SmartFilter;

    protected $fillable = ['title', 'content', 'status', 'views', 'published_at', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function filterableFields(): array
    {
        return [
            'title' => ['operator' => 'like', 'type' => 'string'],
            'content' => ['operator' => 'like', 'type' => 'string'],
            'status' => ['operator' => '=', 'type' => 'string'],
            'views' => ['operator' => '>', 'type' => 'integer'],
            'published_at' => ['operator' => 'date', 'type' => 'date'],
        ];
    }

    public function filterableRelations(): array
    {
        return [
            'user' => [
                'fields' => ['name', 'email'],
                'max_depth' => 1,
            ],
        ];
    }
}
