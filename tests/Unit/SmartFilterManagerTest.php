<?php

namespace Sharifuddin\LaravelSmartFilter\Tests\Unit;

use Sharifuddin\LaravelSmartFilter\Tests\TestCase;
use Sharifuddin\LaravelSmartFilter\SmartFilterManager;
use Sharifuddin\LaravelSmartFilter\Tests\Models\User;
use Illuminate\Http\Request;

class SmartFilterManagerTest extends TestCase
{
    protected SmartFilterManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(SmartFilterManager::class);
    }

    /** @test */
    public function it_can_parse_filters_from_request()
    {
        // Mock request with filter parameters
        $request = new Request([
            'name' => 'John',
            'age' => '25',
            'is_active' => '1',
            'ignored_field' => 'value' // This should be ignored as it's not in allowed filters
        ]);

        $this->app->instance('request', $request);

        $allowedFilters = [
            'name' => ['operator' => 'like', 'type' => 'string'],
            'age' => ['operator' => '=', 'type' => 'integer'],
            'is_active' => ['operator' => '=', 'type' => 'boolean'],
        ];

        $filters = $this->manager->parseFromRequest($allowedFilters);

        $this->assertCount(3, $filters);
        $this->assertEquals('John', $filters['name']['value']);
        $this->assertEquals('like', $filters['name']['operator']);
        $this->assertEquals(25, $filters['age']['value']);
        $this->assertTrue($filters['is_active']['value']);
        $this->assertIsBool($filters['is_active']['value']); // Ensure it's boolean
    }

    /** @test */
    public function it_processes_different_boolean_values_correctly()
    {
        $testCases = [
            '1' => true,
            'true' => true,
            'yes' => true,
            'on' => true,
            '0' => false,
            'false' => false,
            'no' => false,
            'off' => false,
        ];

        foreach ($testCases as $input => $expected) {
            $request = new Request(['is_active' => $input]);
            $this->app->instance('request', $request);

            $allowedFilters = [
                'is_active' => ['operator' => '=', 'type' => 'boolean'],
            ];

            $filters = $this->manager->parseFromRequest($allowedFilters);

            $this->assertCount(1, $filters);
            $this->assertEquals($expected, $filters['is_active']['value'], "Failed for input: {$input}");
            $this->assertIsBool($filters['is_active']['value']);
        }
    }

    /** @test */
    public function it_ignores_empty_values_when_parsing_from_request()
    {
        $request = new Request([
            'name' => '',
            'age' => null,
            'is_active' => '1',
        ]);

        $this->app->instance('request', $request);

        $allowedFilters = [
            'name' => ['operator' => 'like', 'type' => 'string'],
            'age' => ['operator' => '=', 'type' => 'integer'],
            'is_active' => ['operator' => '=', 'type' => 'boolean'],
        ];

        $filters = $this->manager->parseFromRequest($allowedFilters);

        $this->assertCount(1, $filters);
        $this->assertArrayHasKey('is_active', $filters);
        $this->assertArrayNotHasKey('name', $filters);
        $this->assertArrayNotHasKey('age', $filters);
    }

    /** @test */
    public function it_processes_numeric_values_correctly()
    {
        $request = new Request([
            'age' => '25',
            'price' => '19.99',
        ]);

        $this->app->instance('request', $request);

        $allowedFilters = [
            'age' => ['operator' => '=', 'type' => 'integer'],
            'price' => ['operator' => '=', 'type' => 'float'],
        ];

        $filters = $this->manager->parseFromRequest($allowedFilters);

        $this->assertCount(2, $filters);
        $this->assertEquals(25, $filters['age']['value']);
        $this->assertIsInt($filters['age']['value']);
        $this->assertEquals(19.99, $filters['price']['value']);
        $this->assertIsFloat($filters['price']['value']);
    }

    /** @test */
    public function it_processes_array_values_correctly()
    {
        $request = new Request([
            'categories' => '1,2,3',
        ]);

        $this->app->instance('request', $request);

        $allowedFilters = [
            'categories' => ['operator' => 'in', 'type' => 'array'],
        ];

        $filters = $this->manager->parseFromRequest($allowedFilters);

        $this->assertCount(1, $filters);
        $this->assertEquals(['1', '2', '3'], $filters['categories']['value']);
        $this->assertIsArray($filters['categories']['value']);
    }

    /** @test */
    public function it_returns_configuration_values()
    {
        $config = $this->manager->config();
        $this->assertIsArray($config);

        $enabled = $this->manager->config('enabled');
        $this->assertTrue($enabled);

        $default = $this->manager->config('non_existent_key', 'default_value');
        $this->assertEquals('default_value', $default);
    }

    /** @test */
    public function it_checks_if_filtering_is_enabled()
    {
        $this->assertTrue($this->manager->isEnabled());

        config()->set('smart-filter.enabled', false);
        $this->assertFalse($this->manager->isEnabled());
    }

    /** @test */
    public function it_applies_filters_through_manager()
    {
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30,
            'is_active' => true,
            'salary' => 50000.00,
        ]);

        $filters = [
            'name' => ['value' => 'John', 'operator' => 'like', 'type' => 'string']
        ];

        $results = $this->manager->apply(User::query(), $filters)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->name);
    }

    /** @test */
    public function it_throws_exception_for_non_filterable_models()
    {
        $this->expectException(\Sharifuddin\LaravelSmartFilter\Exceptions\SmartFilterException::class);

        // Create a model that doesn't implement Filterable
        $nonFilterableModel = new class extends \Illuminate\Database\Eloquent\Model {};

        $this->manager->apply($nonFilterableModel::query(), []);
    }
}
