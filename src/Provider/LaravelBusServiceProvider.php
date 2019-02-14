<?php

declare(strict_types=1);

namespace Authters\ServiceBus\Provider;

use Authters\ServiceBus\Contract\Manager\ServiceBusManager;
use Authters\ServiceBus\Manager\DefaultBusManager;
use Illuminate\Support\ServiceProvider;

class LaravelBusServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [$this->getConfigPath() => config_path('service_bus.php')],
                'config'
            );
        }
    }

    public function register(): void
    {
        $this->app->singleton(ServiceBusManager::class, DefaultBusManager::class);

        $this->app->alias(ServiceBusManager::class, 'service_bus.manager');
    }

    public function provides(): array
    {
        return [ServiceBusManager::class, 'service_bus.manager'];
    }

    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'service_bus');
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '../../config/service_bus.php';
    }
}