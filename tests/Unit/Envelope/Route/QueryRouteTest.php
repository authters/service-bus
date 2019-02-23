<?php

namespace AuthtersTest\ServiceBus\Unit\Envelope\Route;

use Authters\ServiceBus\Contract\Message\Router\Router;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Envelope\Route\QueryRoute;
use Authters\ServiceBus\Envelope\Route\Route;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\ServiceBus\Support\Events\Named\FinalizedEvent;
use Authters\Tracker\Contract\MessageActionEvent;
use Authters\Tracker\DefaultTracker;
use AuthtersTest\ServiceBus\TestCase;
use AuthtersTest\ServiceBus\Unit\Mock\SomeQueryHandler;
use React\Promise\PromiseInterface;

class QueryRouteTest extends TestCase
{
    /**
     * @test
     */
    public function it_process_message_handler(): void
    {
        $message = 'foo';
        $envelope = $this->buildEnvelope($message);

        $this->assertFalse($envelope->hasReceipt());

        $handler = new SomeQueryHandler();
        $router = new class($handler) implements Router
        {
            private $handler;

            public function __construct($handler)
            {
                $this->handler = $handler;
            }

            public function route(string $messageName): iterable
            {
                yield [$this->handler];
            }
        };

        $route = new QueryRoute($router);
        $nextEnvelope = $this->handleNext($envelope, $route);

        $this->assertEquals($nextEnvelope->getMessage(), $message);
        $this->assertTrue($nextEnvelope->hasReceipt());

        $promise = array_first($envelope->getContent());
        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertEquals($message, $this->extractFromPromise($promise));
    }

    protected function handleNext(Envelope $envelope, Route $route): Envelope
    {
        return $route->handle($envelope, function () use ($envelope) {
            return $envelope;
        });
    }

    protected function buildEnvelope($message): Envelope
    {
        $tracker =new DefaultTracker([
            new DispatchedEvent(), new FinalizedEvent()
        ]);

        $envelope = new Envelope($message, new $tracker);
        $envelope->newActionEvent($this, function (MessageActionEvent $event) use ($message) {
            $event->setMessage($message);
            $event->setMessageName($message);
        });

        return $envelope;
    }

    protected function extractFromPromise(PromiseInterface $promise)
    {
        $res = null;
        $promise->then(function ($data) use (&$res) {
            $res = $data;
        });

        return $res;
    }
}