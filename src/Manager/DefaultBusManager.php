<?php

namespace Authters\ServiceBus\Manager;

use Authters\ServiceBus\Contract\Envelope\Route\Strategy\MessageRouteStrategy;
use Authters\ServiceBus\Contract\Message\MessageProducer;
use Authters\ServiceBus\Contract\Message\Router\Router;
use Authters\ServiceBus\Envelope\Route\Route;
use Authters\ServiceBus\Envelope\Route\RouteStrategy;
use Authters\ServiceBus\Envelope\Route\Strategy\RouteAllAsync;
use Authters\ServiceBus\Envelope\Route\Strategy\RouteNoneAsync;
use Authters\ServiceBus\Envelope\Route\Strategy\RouteOnlyMarkedAsync;
use Authters\ServiceBus\Exception\RuntimeException;
use Authters\ServiceBus\Message\Async\IlluminateProducer;
use Authters\Tracker\Contract\Tracker;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Prooph\Common\Messaging\MessageConverter;
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

    protected function newMessageTracker(string $busType, array $busConfig): Tracker
    {
        $tracker = $this->valueFrom('tracker.service', $busConfig)
            ?? $this->valueFrom('default.tracker.service');

        $defaultTracker = $this->app->make($tracker);

        if($this->app->bound($tracker)){
            return $defaultTracker;
        }

        $this->attachEventsToTracker($busConfig, $defaultTracker);
        $this->attachSubscribersToTracker($busConfig, $defaultTracker);

        return $defaultTracker;
    }

    protected function buildMiddleware(array $busConfig): iterable
    {
        $middleware = $this->buildDefaultsMiddleware($busConfig);
        $middleware [] = [$this->buildRoutes($busConfig), 0];
        $middleware [] = [$this->buildRouteStrategy($busConfig), 1];

        return $this->resolveSortedMiddleware($middleware);
    }

    private function attachEventsToTracker(array $busConfig, Tracker $tracker): void
    {
        $events = $this->valueFrom('tracker.events.named', $busConfig)
            ?? $this->valueFrom('default.tracker.events.named');

        if ($events) {
            foreach ($events as &$event) {
                $tracker->subscribe($this->app->make($event));
            }
        }
    }

    private function attachSubscribersToTracker(array $busConfig, Tracker $tracker): void
    {
        $subscribers = $this->valueFrom('tracker.events.subscribers', $busConfig)
            ?? $this->valueFrom('default.tracker.events.subscribers');

        if ($subscribers) {
            foreach ($subscribers as &$event) {
                $tracker->subscribe($this->app->make($event));
            }
        }
    }

    private function buildDefaultsMiddleware(array $busConfig): array
    {
        return array_merge(
            $this->valueFrom('middleware') ?? [],
            $busConfig['middleware'] ?? []
        );
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
        $strategy = $this->valueFrom('message.route_strategy', $busConfig)
            ?? $this->valueFrom('default.message.route_strategy');

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
        $messageProducerId = $this->valueFrom('message.producer', $busConfig) ??
            $this->valueFrom('default.message.producer');

        if ($this->app->bound($messageProducerId)) {
            return $this->app->make($messageProducerId);
        }

        $messageConverterId = $this->valueFrom('message.converter', $busConfig) ??
            $this->valueFrom('default.message.converter');

        $this->app->bindIf(MessageConverter::class, $messageConverterId);

        return $this->app->make(IlluminateProducer::class);
    }

    private function buildRoutes(array $busConfig): Route
    {
        $routeId = $busConfig['route'];
        if ($this->app->bound($routeId)) {
            return $this->app->make($routeId);
        }

        if (!$routeId || !class_exists($routeId)) {
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

        if (!$router || !class_exists($router)) {
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
        $serviceLocator = $this->valueFrom('message.handler.resolver', $busConfig)
            ?? $this->valueFrom('default.message.handler.resolver');

        if (!$serviceLocator) {
            return null;
        }

        return $this->app->make($serviceLocator);
    }

    private function determineCallableHandler(array $busConfig): ?callable
    {
        $strategy = $this->valueFrom('message.handler.to_callable', $busConfig) ??
            $this->valueFrom('default.message.handler.to_callable');

        return $strategy ? $this->app->make($strategy) : null;
    }

    private function determineCollectibleExceptions(array $busConfig): bool
    {
        $collectible = $this->valueFrom('collect_exceptions', $busConfig) ??
            $this->valueFrom('default.collect_exceptions');

        return true === $collectible ?? false;
    }

    private function determineNullableMessageHandler(array $busConfig): bool
    {
        $allowNullHandler = $this->valueFrom('message.handler.allow_null', $busConfig) ??
            $this->valueFrom('default.message.handler.allow_null');

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