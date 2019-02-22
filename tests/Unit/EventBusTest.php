<?php

namespace AuthtersTest\ServiceBus\Unit;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Envelope\Bootstrap\ContentHandlerBootstrap;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Envelope\Route\EventRoute;
use Authters\ServiceBus\Envelope\Route\RouteStrategy;
use Authters\ServiceBus\Envelope\Route\Strategy\RouteNoneAsync;
use Authters\ServiceBus\EventBus;
use Authters\ServiceBus\Message\Router\MultipleHandlerRouter;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\ServiceBus\Support\Events\Named\FinalizedEvent;
use Authters\Tracker\Contract\ActionEvent;
use AuthtersTest\ServiceBus\TestCase;
use AuthtersTest\ServiceBus\Unit\Mock\SomeMessageHandler;

class EventBusTest extends TestCase
{
    /**
     * @test
     */
    public function it_dispatch_message(): void
    {
        $bus = new EventBus($this->getMiddleware());

        $bus->dispatch($this->message);

        foreach ($this->handlers as $handler){
            $this->assertEquals($this->message, $handler->getMessage());
        }
    }

    protected function getMiddleware(): iterable
    {
        return [
            new ContentHandlerBootstrap(),
            $this->getTrackerBootstrap(),
            new EventRoute(new MultipleHandlerRouter($this->map)),
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
    private $handlers = [];

    protected function setUp()
    {
        $this->message = 'foo_bar';
        $this->handlers = [
            new SomeMessageHandler(),
            new SomeMessageHandler()
        ];
        $this->map = [$this->message => $this->handlers];
    }
}