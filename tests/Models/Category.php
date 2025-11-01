<?php

namespace Sharifuddin\LaravelSmartFilter\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'is_active'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
