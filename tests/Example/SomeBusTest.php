<?php

namespace AuthtersTest\ServiceBus\Example;

use Authters\ServiceBus\Envelope\Bootstrap\ContentHandlerBootstrap;
use Authters\ServiceBus\Envelope\Bootstrap\MessageTrackerBootstrap;
use Authters\ServiceBus\Envelope\Route\CommandRoute;
use Authters\ServiceBus\Envelope\Route\Route;
use Authters\ServiceBus\Envelope\Route\RouteStrategy;
use Authters\ServiceBus\Envelope\Route\Strategy\RouteNoneAsync;
use Authters\ServiceBus\Message\Router\SingleHandlerRouter;
use Authters\ServiceBus\Tracker\MessageTracker;
use AuthtersTest\ServiceBus\Example\Mock\SomeBus;
use AuthtersTest\ServiceBus\Example\Mock\SomeMessage;
use AuthtersTest\ServiceBus\Example\Mock\SomeMessageHandler;
use PHPUnit\Framework\TestCase;

class SomeBusTest extends TestCase
{
    private $map = [];
    private $message;
    private $handler;

    protected function setUp()
    {
        $this->message = new SomeMessage('dispatch me');
        $this->handler = new SomeMessageHandler();
        $this->map = [\get_class($this->message) => $this->handler];
    }

    /**
     * @test
     */
    public function it_dispatch_object(): void
    {
        $bus = new SomeBus($this->getMiddleware(), new MessageTracker());

        $bus->dispatch($this->message);

        $this->assertEquals($this->message, $this->handler->getMessage());
    }

    /**
     * @test
     */
    public function it_dispatch_string(): void
    {
        $message = 'dispatch_me';
        $handler = new SomeMessageHandler();
        $this->map = [$message => $handler];

        $bus = new SomeBus($this->getMiddleware(), new MessageTracker());

        $bus->dispatch($message);

        $this->assertEquals($message, $handler->getMessage());
    }

    protected function getMiddleware(): iterable
    {
        return [
            new ContentHandlerBootstrap(),
            new MessageTrackerBootstrap(),
            $this->getRoute(),
            $this->getRouteStrategyDecorator()
        ];
    }

    protected function getRoute(): Route
    {
        return new CommandRoute(new SingleHandlerRouter($this->map));
    }

    protected function getRouteStrategyDecorator(): RouteStrategy
    {
        return new RouteStrategy(new RouteNoneAsync());
    }
}