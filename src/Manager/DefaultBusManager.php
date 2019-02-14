<?php

namespace Authters\ServiceBus\Manager;

use Authters\ServiceBus\Contract\Envelope\Route\Strategy\MessageRouteStrategy;
use Authters\ServiceBus\Contract\Message\MessageProducer;
use Authters\ServiceBus\Contract\Message\Router\Router;
use Authters\ServiceBus\Contract\Tracker\Tracker;
use Authters\ServiceBus\Envelope\Route\Route;
use Authters\ServiceBus\Envelope\Route\RouteStrategy;
use Authters\ServiceBus\Envelope\Route\Strategy\RouteAllAsync;
use Authters\ServiceBus\Envelope\Route\Strategy\RouteNoneAsync;
use Authters\ServiceBus\Envelope\Route\Strategy\RouteOnlyMarkedAsync;
use Authters\ServiceBus\Exception\RuntimeException;
use Authters\ServiceBus\Message\Async\IlluminateProducer;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Psr\Container\ContainerInterface;

abstract class DefaultBusManager
{
    use HasConfig;

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var array
     */
    protected $buses = [];

    public function __construct(Container $app, string $namespace = 'authters')
    {
        $this->app = $app;
        $this->namespace = $namespace;
    }

    protected function newMessageTracker(string $busType): Tracker
    {
        $tracker = $busConfig['message.tracker']
            ?? $this->defaultParameterSwitcher('default.message.tracker');

        return $this->app->make($tracker);
    }

    protected function buildMiddleware(array $busConfig): iterable
    {
        $middleware = $this->buildDefaultsMiddleware($busConfig);
        $middleware [] = [$this->buildRoutes($busConfig), 0];
        $middleware [] = [$this->buildRouteStrategy($busConfig), 1];

        return $this->resolveSortedMiddleware($middleware);
    }

    private function buildDefaultsMiddleware(array $busConfig): array
    {
        $middleware = $this->defaultParameterSwitcher('middleware') ?? [];

        return array_merge($middleware, $busConfig['middleware'] ?? []);
    }

    private function buildRouteStrategy(array $busConfig): RouteStrategy
    {
        if ($route = $this->determineRouteStrategy($busConfig)) {
            return new RouteStrategy($route);
        }

        return null;
    }

    private function determineRouteStrategy(array $busConfig): ?MessageRouteStrategy
    {
        $strategy = $busConfig['route_strategy']
            ?? $this->defaultParameterSwitcher('default.route_strategy');

        if (!$strategy) {
            return null;
        }

        if ($this->app->bound($strategy)) {
            return $this->app->make($strategy);
        }

        if ($strategy === MessageRouteStrategy::ROUTE_NONE_ASYNC) {
            return new RouteNoneAsync();
        }

        $messageProducer = $this->determineMessageProducer($busConfig);

        switch ($strategy) {
            case MessageRouteStrategy::ROUTE_ONLY_ASYNC:
                return new RouteOnlyMarkedAsync($messageProducer);

            case MessageRouteStrategy::ROUTE_ALL_ASYNC:
                return new RouteAllAsync($messageProducer);
        };

        throw new RuntimeException("Invalid route strategy $strategy");
    }

    private function determineMessageProducer(array $busConfig): MessageProducer
    {
        // is this useful to make it configure per bus ???
        $messageProducerId = $busConfig['message.producer'] ??
            $this->defaultParameterSwitcher('default.message.producer');

        if ($this->app->bound($messageProducerId)) {
            return $this->app->make($messageProducerId);
        }

        $messageConverterId = $busConfig['message.converter'] ??
            $this->defaultParameterSwitcher('default.message.converter');

        return new IlluminateProducer(
            $this->app->make(QueueingDispatcher::class),
            $this->app->make($messageConverterId)
        );
    }

    private function buildRoutes(array $busConfig): Route
    {
        $routeId = $busConfig['route'];

        if ($this->app->bound($routeId)) {
            return $this->app->make($routeId);
        }

        if (!$routeId || !class_exists($routeId) /*|| !$routeId instanceof Route*/) {
            throw new RuntimeException("Invalid route $routeId in service bus config");
        }

        $route = new $routeId(
            $this->buildRouter($busConfig),
            $this->determineCallableHandler($busConfig),
            $this->determineCollectibleExceptions($busConfig)
        );

        return $route;
    }

    private function buildRouter(array $busConfig): Router
    {
        $router = $busConfig['router'];
        if ($this->app->bound($router)) {
            return $this->app->make($router);
        }

        if (!$router || !class_exists($router) /* || !$router instanceof Router*/) {
            throw new RuntimeException("Invalid router $router in service bus config");
        }

        return new $router(
            $busConfig['routes'] ?? [],
            $this->determineServiceLocator($busConfig),
            $this->determineNullableMessageHandler($busConfig)
        );
    }

    private function determineServiceLocator(array $busConfig): ?ContainerInterface
    {
        $serviceLocator = $busConfig['service_locator']
            ?? $this->defaultParameterSwitcher('default.service_locator');

        if (!$serviceLocator) {
            return null;
        }

        return $this->app->make($serviceLocator);
    }

    private function determineCallableHandler(array $busConfig): ?callable
    {
        $strategy = $busConfig['callable_handler'] ??
            $this->defaultParameterSwitcher('default.callable_handler');

        return $strategy ? $this->app->make($strategy) : null;
    }

    private function determineCollectibleExceptions(array $busConfig): bool
    {
        $collectible = $busConfig['collect_exceptions'] ??
            $this->defaultParameterSwitcher('default.collect_exceptions');

        return true === $collectible ?? false;
    }

    private function determineNullableMessageHandler(array $busConfig): bool
    {
        // checkMe reserve to multiple handlers router / Event bus
        $allowNullHandler = $busConfig['allow_null_handler'] ??
            $this->defaultParameterSwitcher('default.allow_null_handler');

        return true === $allowNullHandler ?? false;
    }

    private function resolveSortedMiddleware(iterable $middleware): iterable
    {
        return (new Collection($middleware))
            ->sortByDesc(function (array $stack) {
                [, $priority] = $stack;

                return $priority;
            })
            ->transform(function (array $stack) {
                [$middleware] = $stack;

                if (is_string($middleware)) {
                    $middleware = $this->app->make($middleware);
                }

                return $middleware;
            })
            ->values()
            ->toArray();
    }
}