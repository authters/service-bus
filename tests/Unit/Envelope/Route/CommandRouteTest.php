<?php

namespace AuthtersTest\ServiceBus\Unit\Envelope\Route;

use Authters\ServiceBus\Contract\Message\Router\Router;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Envelope\Route\CommandRoute;
use Authters\ServiceBus\Envelope\Route\Route;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\ServiceBus\Support\Events\Named\FinalizedEvent;
use Authters\Tracker\Contract\MessageActionEvent;
use Authters\Tracker\DefaultTracker;
use AuthtersTest\ServiceBus\TestCase;
use AuthtersTest\ServiceBus\Unit\Mock\SomeMessageHandler;

class CommandRouteTest extends TestCase
{
    /**
     * @test
     */
    public function it_process_message_handler(): void
    {
        $message = 'foo';
        $envelope = $this->buildEnvelope($message);

        $this->assertFalse($envelope->hasReceipt());

        $handler = new SomeMessageHandler();
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

        $route = new CommandRoute($router);
        $nextEnvelope = $this->handleNextRoute($envelope, $route);

        $this->assertEquals($nextEnvelope->getMessage(), $message);
        $this->assertTrue($nextEnvelope->hasReceipt());
        $this->assertEquals($message, $handler->getMessage());
    }

    /**
     * checkMe Route does not use @method supportBus
     */
    public function it_expects_command_bus_type_from_envelope(): void
    {
    }

    protected function handleNextRoute(Envelope $envelope, Route $route): Envelope
    {
        return $route->handle($envelope, function () use ($envelope) {
            return $envelope;
        });
    }

    protected function buildEnvelope($message): Envelope
    {
        $tracker = new DefaultTracker([
            new DispatchedEvent(), new FinalizedEvent()
        ]);

        $envelope = new Envelope($message, $tracker);
        $envelope->newActionEvent($this, function (MessageActionEvent $event) use ($message) {
            $event->setMessage($message);
            $event->setMessageName($message);
        });

        return $envelope;
    }
}