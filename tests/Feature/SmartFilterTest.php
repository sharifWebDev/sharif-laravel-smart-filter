<?php

namespace Sharifuddin\LaravelSmartFilter\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sharifuddin\LaravelSmartFilter\Tests\Models\Post;
use Sharifuddin\LaravelSmartFilter\Tests\Models\User;
use Sharifuddin\LaravelSmartFilter\Tests\TestCase;

class SmartFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;

    protected User $user2;

    protected User $user3;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30,
            'is_active' => true,
            'salary' => 50000.00,
        ]);

        $this->user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'age' => 25,
            'is_active' => false,
            'salary' => 60000.00,
        ]);

        $this->user3 = User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'age' => 35,
            'is_active' => true,
            'salary' => 70000.00,
        ]);

        // Create test posts
        Post::create([
            'title' => 'First Post',
            'content' => 'This is the first post content',
            'status' => 'published',
            'views' => 100,
            'published_at' => '2023-01-01 10:00:00',
            'user_id' => $this->user1->id,
        ]);

        Post::create([
            'title' => 'Second Post',
            'content' => 'This is the second post content',
            'status' => 'draft',
            'views' => 50,
            'published_at' => '2023-02-01 10:00:00',
            'user_id' => $this->user2->id,
        ]);

        Post::create([
            'title' => 'Third Post',
            'content' => 'This is the third post about programming',
            'status' => 'published',
            'views' => 200,
            'published_at' => '2023-03-01 10:00:00',
            'user_id' => $this->user3->id,
        ]);
    }

    /** @test */
    public function it_can_filter_by_string_field_with_like_operator()
    {
        $filters = [
            'name' => ['value' => 'John', 'operator' => 'like', 'type' => 'string'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->name);
    }

    /** @test */
    public function it_can_filter_by_exact_match()
    {
        $filters = [
            'email' => ['value' => 'john@example.com', 'operator' => '=', 'type' => 'string'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('john@example.com', $results->first()->email);
    }

    /** @test */
    public function it_can_filter_by_integer_field()
    {
        $filters = [
            'age' => ['value' => 30, 'operator' => '=', 'type' => 'integer'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals(30, $results->first()->age);
    }

    /** @test */
    public function it_can_filter_by_boolean_field()
    {
        $filters = [
            'is_active' => ['value' => true, 'operator' => '=', 'type' => 'boolean'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($user) => $user->is_active === true));
    }

    /** @test */
    public function it_can_filter_with_greater_than_operator()
    {
        $filters = [
            'age' => ['value' => 30, 'operator' => '>', 'type' => 'integer'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Bob Johnson', $results->first()->name);
    }

    /** @test */
    public function it_can_filter_with_less_than_operator()
    {
        $filters = [
            'age' => ['value' => 30, 'operator' => '<', 'type' => 'integer'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Jane Smith', $results->first()->name);
    }

    /** @test */
    public function it_can_filter_with_between_operator()
    {
        $filters = [
            'salary' => ['value' => [55000, 65000], 'operator' => 'between', 'type' => 'float'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Jane Smith', $results->first()->name);
    }

    /** @test */
    public function it_can_filter_by_relation_fields()
    {
        $filters = [
            'user.name' => ['value' => 'John', 'operator' => 'like', 'type' => 'string'],
        ];

        $results = Post::applySmartFilters($filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('First Post', $results->first()->title);
    }

    /** @test */
    public function it_handles_empty_filters_gracefully()
    {
        $results = User::applySmartFilters([])->get();

        $this->assertCount(3, $results); // Should return all records
    }

    /** @test */
    public function it_respects_max_relation_depth_configuration()
    {
        $filters = [
            'user.name' => ['value' => 'John', 'operator' => 'like', 'type' => 'string'],
        ];

        $results = Post::applySmartFilters($filters, ['max_relation_depth' => 0])->get();

        $this->assertCount(0, $results); // No results because relation depth is 0
    }

    /** @test */
    public function it_can_use_builder_macros()
    {
        $filters = [
            'name' => ['value' => 'John', 'operator' => 'like', 'type' => 'string'],
        ];

        $results = User::smartFilter($filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->name);
    }

    /** @test */
    public function it_can_filter_with_not_equal_operator()
    {
        $filters = [
            'name' => ['value' => 'John Doe', 'operator' => '!=', 'type' => 'string'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(2, $results);
        $this->assertFalse($results->contains('name', 'John Doe'));
    }

    /** @test */
    public function it_can_filter_with_in_operator()
    {
        $filters = [
            'age' => ['value' => [25, 35], 'operator' => 'in', 'type' => 'integer'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['Jane Smith', 'Bob Johnson'], $results->pluck('name')->sort()->values()->toArray());
    }

    /** @test */
    public function it_can_filter_with_not_in_operator()
    {
        $filters = [
            'age' => ['value' => [25, 35], 'operator' => 'not_in', 'type' => 'integer'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->name);
    }

    /** @test */
    public function it_can_filter_with_null_operator()
    {
        // Create a user with null age
        User::create([
            'name' => 'Null Age User',
            'email' => 'nullage@example.com',
            'age' => null,
            'is_active' => true,
            'salary' => 40000.00,
        ]);

        $filters = [
            'age' => ['value' => null, 'operator' => 'null', 'type' => 'integer'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Null Age User', $results->first()->name);
    }

    /** @test */
    public function it_can_filter_with_not_null_operator()
    {
        $filters = [
            'age' => ['value' => null, 'operator' => 'not_null', 'type' => 'integer'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(3, $results); // All initial users have age set
    }

    /** @test */
    public function it_can_filter_by_date_field()
    {
        $filters = [
            'published_at' => ['value' => '2023-01-01', 'operator' => 'date', 'type' => 'date'],
        ];

        $results = Post::applySmartFilters($filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('First Post', $results->first()->title);
    }

    /** @test */
    public function it_processes_string_values_correctly_for_like_operator()
    {
        $filters = [
            'name' => ['value' => 'John', 'operator' => 'like', 'type' => 'string'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(1, $results);
        // The value should be automatically wrapped with % for LIKE operator
    }

    /** @test */
    public function it_processes_array_values_correctly_for_in_operator()
    {
        $filters = [
            'age' => ['value' => '25,35', 'operator' => 'in', 'type' => 'array'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(2, $results);
    }

    /** @test */
    public function it_ignores_non_filterable_fields()
    {
        $filters = [
            'non_existent_field' => ['value' => 'test', 'operator' => '=', 'type' => 'string'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(3, $results); // Should return all records, ignoring invalid field
    }

    /** @test */
    public function it_can_be_disabled_globally()
    {
        config()->set('smart-filter.enabled', false);

        $filters = [
            'name' => ['value' => 'John', 'operator' => 'like', 'type' => 'string'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(3, $results); // Should return all records when disabled
    }

    /** @test */
    public function it_uses_custom_model_configuration()
    {
        $filters = [
            'name' => ['value' => 'John', 'operator' => 'like', 'type' => 'string'],
        ];

        $results = User::applySmartFilters($filters)->get();

        // User model has strict_mode set to true in filterConfig
        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_works_with_multiple_filters_combined()
    {
        $filters = [
            'is_active' => ['value' => true, 'operator' => '=', 'type' => 'boolean'],
            'age' => ['value' => 30, 'operator' => '>=', 'type' => 'integer'],
        ];

        $results = User::applySmartFilters($filters)->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['John Doe', 'Bob Johnson'], $results->pluck('name')->sort()->values()->toArray());
    }
}
