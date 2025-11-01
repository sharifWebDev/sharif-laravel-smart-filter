<?php

namespace Sharifuddin\LaravelSmartFilter\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Sharifuddin\LaravelSmartFilter\Tests\Models\Category;
use Sharifuddin\LaravelSmartFilter\Tests\Models\Product;
use Sharifuddin\LaravelSmartFilter\Tests\Models\User;
use Sharifuddin\LaravelSmartFilter\Tests\TestCase;

class SmartSearchTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected $category;

    protected $products;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->category = Category::factory()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $this->products = Product::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        Product::factory()->create([
            'name' => 'Wireless Keyboard',
            'description' => 'A mechanical wireless keyboard',
            'sku' => 'KB-WIRELESS-001',
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);
    }

    /** @test */
    public function it_performs_basic_search()
    {
        $results = Product::applySmartSearch('Keyboard')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Wireless Keyboard', $results->first()->name);
    }

    /** @test */
    public function it_searches_multiple_columns()
    {
        $results = Product::applySmartSearch('KB-WIRELESS')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Wireless Keyboard', $results->first()->name);
    }

    /** @test */
    public function it_searches_through_relations()
    {
        $results = Product::applySmartSearch('John')->get();

        $this->assertCount(6, $results); // All products belong to John
    }

    /** @test */
    public function it_respects_max_relation_depth()
    {
        $results = Product::applySmartSearch('John', [], [
            'max_relation_depth' => 0,
        ])->get();

        $this->assertCount(0, $results); // No local matches for "John"
    }

    /** @test */
    public function it_handles_different_search_modes()
    {
        // Exact match
        $results = Product::applySmartSearch('Wireless Keyboard', [], [
            'mode' => 'exact',
        ])->get();

        $this->assertCount(1, $results);

        // Starts with
        $results = Product::applySmartSearch('Wireless', [], [
            'mode' => 'starts_with',
        ])->get();

        $this->assertCount(1, $results);

        // Ends with
        $results = Product::applySmartSearch('Keyboard', [], [
            'mode' => 'ends_with',
        ])->get();

        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_respects_custom_columns()
    {
        $results = Product::applySmartSearch('KB-WIRELESS', ['sku'])->get();

        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_handles_empty_search_gracefully()
    {
        $results = Product::applySmartSearch('')->get();

        $this->assertCount(6, $results); // Returns all records
    }

    /** @test */
    public function it_works_with_and_operator()
    {
        $results = Product::applySmartSearch('Wireless Keyboard', [], [
            'search_operator' => 'and',
        ])->get();

        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_uses_builder_macros()
    {
        $results = Product::smartSearch('Keyboard')->get();

        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_validates_search_term_length()
    {
        $this->expectException(\InvalidArgumentException::class);

        Product::applySmartSearch('a')->get();
    }

    /** @test */
    public function it_can_be_disabled_globally()
    {
        config()->set('smart-search.enabled', false);

        $results = Product::applySmartSearch('Keyboard')->get();

        $this->assertCount(6, $results); // Returns all records when disabled
    }
}
