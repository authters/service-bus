<?php

namespace AuthtersTest\ServiceBus\Unit;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Envelope\Bootstrap\ContentHandlerBootstrap;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Envelope\Route\QueryRoute;
use Authters\ServiceBus\Envelope\Route\RouteStrategy;
use Authters\ServiceBus\Envelope\Route\Strategy\RouteNoneAsync;
use Authters\ServiceBus\Message\Router\SingleHandlerRouter;
use Authters\ServiceBus\QueryBus;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\ServiceBus\Support\Events\Named\FinalizedEvent;
use Authters\Tracker\Contract\MessageActionEvent;
use AuthtersTest\ServiceBus\TestCase;
use AuthtersTest\ServiceBus\Unit\Mock\SomeQueryHandler;
use React\Promise\PromiseInterface;

class QueryBusTest extends TestCase
{
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
            $this->getTrackerBootstrap(),
            new QueryRoute(new SingleHandlerRouter($this->map)),
            new RouteStrategy(new RouteNoneAsync())
        ];
    }

    protected function handleResult(PromiseInterface $promise)
    {
        $data = null;
        $promise->then(function ($result) use (&$data) {
            $data = $result;
        });

        return $data;
    }

    protected function getTrackerBootstrap(): Middleware
    {
        return new class() implements Middleware
        {
            public function handle(Envelope $envelope, callable $next)
            {
                $tracker = $envelope->getTracker();
                $tracker->subscribe(new DispatchedEvent());
                $tracker->subscribe(new FinalizedEvent());
                $message = $envelope->getMessage();

                $event = $envelope->newActionEvent($this, function (MessageActionEvent $event) use ($message) {
                    $event->setMessage($message);
                    $event->setMessageName($message);
                });

                $tracker->emit($event);

                $envelope = $next($envelope);

                $event->setEvent(new FinalizedEvent());
                $tracker->emit($event);

                return $envelope;
            }
        };
    }

    private $map = [];
    private $message;
    private $handler;

    protected function setUp()
    {
        $this->message = 'foo_bar';
        $this->handler = new SomeQueryHandler();
        $this->map = [$this->message => $this->handler];
    }
}