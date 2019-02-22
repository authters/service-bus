<?php

namespace AuthtersTest\ServiceBus\Unit;

use Authters\ServiceBus\CommandBus;
use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Envelope\Bootstrap\ContentHandlerBootstrap;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Envelope\Route\CommandRoute;
use Authters\ServiceBus\Envelope\Route\RouteStrategy;
use Authters\ServiceBus\Envelope\Route\Strategy\RouteNoneAsync;
use Authters\ServiceBus\Message\Router\SingleHandlerRouter;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\ServiceBus\Support\Events\Named\FinalizedEvent;
use Authters\Tracker\Contract\ActionEvent;
use AuthtersTest\ServiceBus\TestCase;
use AuthtersTest\ServiceBus\Unit\Mock\SomeMessageHandler;

class CommandBusTest extends TestCase
{
    /**
     * @test
     */
    public function it_dispatch_message(): void
    {
        $bus = new CommandBus($this->getMiddleware());

        $bus->dispatch($this->message);

        $this->assertEquals($this->message, $this->handler->getMessage());
    }

    protected function getMiddleware(): iterable
    {
        return [
            new ContentHandlerBootstrap(),
            $this->getTrackerBootstrap(),
            new CommandRoute(new SingleHandlerRouter($this->map)),
            new RouteStrategy(new RouteNoneAsync())
        ];
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

                $event = $envelope->newActionEvent($this, function (ActionEvent $event) use ($message) {
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
    /**
     * @var SomeMessageHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->message = 'foo_bar';
        $this->handler = new SomeMessageHandler();
        $this->map = [$this->message => $this->handler];
    }
}