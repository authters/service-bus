<?php

namespace AuthtersTest\ServiceBus\Unit;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Envelope\Bootstrap\ContentHandlerBootstrap;
use Authters\ServiceBus\Envelope\Bootstrap\MessageTrackerBootstrap;
use Authters\ServiceBus\Envelope\Route\QueryRoute;
use Authters\ServiceBus\Envelope\Route\Route;
use Authters\ServiceBus\Envelope\Route\RouteStrategy;
use Authters\ServiceBus\Envelope\Route\Strategy\RouteNoneAsync;
use Authters\ServiceBus\Message\Router\SingleHandlerRouter;
use Authters\ServiceBus\QueryBus;
use AuthtersTest\ServiceBus\TestCase;
use AuthtersTest\ServiceBus\Unit\Mock\SomeQueryHandler;
use React\Promise\PromiseInterface;

class QueryBusTest extends TestCase
{
   private $map = [];
   private $message;
   private $handler;

   protected function setUp()
   {
       $this->message = 'foo_bar';
       $this->handler = new SomeQueryHandler();
       $this->map = [$this->message => $this->handler];
   }

    /**
     * @test
     */
    public function it_dispatch_message(): void
    {
        $bus = new QueryBus($this->getMiddleware());
        $promise = $bus->dispatch($this->message);

        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertEquals($this->message, $this->handleResult($promise));
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
        return new QueryRoute(new SingleHandlerRouter($this->map));
    }

    protected function getRouteStrategyDecorator(): Middleware
    {
        return new RouteStrategy(new RouteNoneAsync());
    }

    protected function handleResult(PromiseInterface $promise)
    {
        $data = null;
        $promise->then(function ($result) use (&$data) {
            $data = $result;
        });

        return $data;
    }
}