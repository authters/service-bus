<?php

namespace AuthtersTest\ServiceBus\Unit\Envelope\Route;

use Authters\ServiceBus\Contract\Message\Router\Router;
use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Envelope\Route\Handler\CallableHandler;
use Authters\ServiceBus\Envelope\Route\Route;
use Authters\ServiceBus\Tracker\DefaultMessageTracker;
use AuthtersTest\ServiceBus\Example\Mock\SomeMessageHandler;
use AuthtersTest\ServiceBus\Example\Mock\SomeRoute;
use AuthtersTest\ServiceBus\TestCase;

class RouteTest extends TestCase
{
    /**
     * @test
     */
    public function it_mark_message_received_into_envelope(): void
    {
        $message = 'foo';
        $envelope = $this->dispatchWithMessage($message);

        $this->assertFalse($envelope->hasReceipt());

        $route = new SomeRoute($this->getRouterInstance());
        $nextEnvelope = $this->handleNext($envelope, $route);

        $this->assertEquals($nextEnvelope->getMessage(), $message);
        $this->assertTrue($nextEnvelope->hasReceipt());
    }

    public function it_raise_multiple_exceptions(): void
    {

    }

    /**
     * @test
     */
    public function it_transform_handler_to_a_valid_callable(): void
    {
        $message = 'foo';

        $envelope = $this->dispatchWithMessage($message);

        $this->assertFalse($envelope->hasReceipt());

        $toCallable = new CallableHandler('fooBar');

        $router = new class() implements Router
        {
            public function route(string $messageName): iterable
            {
                yield[
                    new class()
                    {
                        public function fooBar($message)
                        {
                        }
                    }
                ];
            }
        };

        $route = new SomeRoute($router, $toCallable);
        $nextEnvelope = $this->handleNext($envelope, $route);

        $this->assertEquals($nextEnvelope->getMessage(), $message);
        $this->assertTrue($nextEnvelope->hasReceipt());
    }

    public function it_raise_exception_when_handler_is_not_valid(): void
    {

    }

    public function getRouterInstance(): Router
    {
        return new class() implements Router
        {
            public function route(string $messageName): iterable
            {
                yield [new SomeMessageHandler()];
            }
        };
    }

    protected function handleNext(Envelope $envelope, Route $route): Envelope
    {
        return $route->handle($envelope, function () use ($envelope) {
            return $envelope;
        });
    }

    protected function dispatchWithMessage($message): Envelope
    {
        $envelope = new Envelope($message, new DefaultMessageTracker());
        $event = $envelope->newActionEvent($this, function (MessageActionEvent $event) use ($message) {
            $event->setMessage($message);
        });

        $envelope->dispatching($event);

        return $envelope;
    }
}