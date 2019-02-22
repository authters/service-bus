<?php

namespace AuthtersTest\ServiceBus\Unit\Envelope\Route;

use Authters\ServiceBus\Contract\Message\Router\Router;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Envelope\Route\Handler\CallableHandler;
use Authters\ServiceBus\Envelope\Route\Route;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\ServiceBus\Support\Events\Named\FinalizedEvent;
use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\DefaultTracker;
use AuthtersTest\ServiceBus\TestCase;
use AuthtersTest\ServiceBus\Unit\Mock\SomeMessageHandler;
use AuthtersTest\ServiceBus\Unit\Mock\SomeRoute;

class RouteTest extends TestCase
{
    /**
     * @test
     */
    public function it_mark_message_received_into_envelope(): void
    {
        $message = 'foo';
        $envelope = $this->buildEnvelope($message);

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

        $envelope = $this->buildEnvelope($message);

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

    protected function buildEnvelope($message): Envelope
    {
        $tracker =new DefaultTracker([
            new DispatchedEvent(), new FinalizedEvent()
        ]);

        $envelope = new Envelope($message, $tracker);
        $event = $envelope->newActionEvent($this, function (ActionEvent $event) use ($message) {
            $event->setMessage($message);
            $event->setMessageName($message);
        });

        $tracker->emit($event);

        return $envelope;
    }
}