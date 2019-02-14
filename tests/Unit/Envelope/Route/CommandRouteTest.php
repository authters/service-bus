<?php

namespace AuthtersTest\ServiceBus\Unit\Envelope\Route;

use Authters\ServiceBus\CommandBus;
use Authters\ServiceBus\Contract\Message\Router\Router;
use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Envelope\Route\CommandRoute;
use Authters\ServiceBus\Envelope\Route\Route;
use Authters\ServiceBus\Tracker\MessageTracker;
use AuthtersTest\ServiceBus\Example\Mock\SomeMessageHandler;
use AuthtersTest\ServiceBus\TestCase;

class CommandRouteTest extends TestCase
{
    /**
     * @test
     */
    public function it_process_message_handler(): void
    {
        $message = 'foo';
        $envelope = $this->dispatchWithMessage($message);

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
        $nextEnvelope = $this->handleNext($envelope, $route);

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

    protected function handleNext(Envelope $envelope, Route $route): Envelope
    {
        return $route->handle($envelope, function () use ($envelope) {
            return $envelope;
        });
    }

    protected function dispatchWithMessage($message): Envelope
    {
        $envelope = new Envelope($message, new MessageTracker());
        $event = $envelope->newActionEvent($this, function (MessageActionEvent $event) use ($message) {
            $event->setMessage($message);
        });

        $envelope->dispatching($event);

        return $envelope;
    }
}