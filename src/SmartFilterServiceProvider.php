<?php

namespace Sharifuddin\LaravelSmartFilter;

use Illuminate\Support\ServiceProvider;
use Sharifuddin\LaravelSmartFilter\Macros\BuilderMacros;

class SmartFilterServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/smart-filter.php',
            'smart-filter'
        );

        $this->app->singleton('smart-filter', function ($app) {
            return new SmartFilterManager($app);
        });

        $this->app->alias('smart-filter', SmartFilterManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishResources();
        }

        $this->registerMacros();
    }

    /**
     * Publish package resources.
     */
    protected function publishResources(): void
    {
        $this->publishes([
            __DIR__.'/../config/smart-filter.php' => config_path('smart-filter.php'),
        ], 'smart-filter-config');
    }

    /**
     * Register query builder macros.
     */
    protected function registerMacros(): void
    {
        if (class_exists(BuilderMacros::class)) {
            BuilderMacros::register();
        }
    }
}
