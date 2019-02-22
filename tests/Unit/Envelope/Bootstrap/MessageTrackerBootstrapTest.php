<?php

namespace AuthtersTest\ServiceBus\Unit\Envelope\Bootstrap;

use Authters\ServiceBus\Envelope\Bootstrap\MessageTrackerBootstrap;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Exception\RuntimeException;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\ServiceBus\Support\Events\Named\FinalizedEvent;
use Authters\Tracker\DefaultTracker;
use AuthtersTest\ServiceBus\TestCase;

class MessageTrackerBootstrapTest extends TestCase
{
    /**
     * @test
     */
    public function it_initialize_message_event(): void
    {
        $events = new MessageTrackerBootstrap();
        $envelope = $this->getEnvelope($message = 'foo');

        $next = function (Envelope $currentEnvelope) {
            $this->assertInstanceOf(
                DispatchedEvent::class,
                $currentEnvelope->currentActionEvent()->currentEvent()
            );

            $this->assertEquals('foo', $currentEnvelope->currentActionEvent()->message());

            return $currentEnvelope;
        };

        $envelopeDispatched = $events->handle($envelope, $next);
        $this->assertEquals($message, $envelopeDispatched->currentActionEvent()->message());
    }

    /**
     * @test
     */
    public function it_finalize_message_event(): void
    {
        $events = new MessageTrackerBootstrap();
        $envelope = $this->getEnvelope($message = 'foo');

        $next = function (Envelope $currentEnvelope) {
            return $currentEnvelope;
        };

        $envelopeDispatched = $events->handle($envelope, $next);
        $this->assertEquals($message, $envelopeDispatched->currentActionEvent()->message());

        $finalized = $next($envelopeDispatched);

        $this->assertInstanceOf(FinalizedEvent::class, $finalized->currentActionEvent()->currentEvent());
        $this->assertTrue($finalized->currentActionEvent()->isPropagationStopped());
    }

    /**
     * @test
     */
    public function it_catch_exception_caught_while_dispatching(): void
    {
        $message = 'foo';
        $events = new MessageTrackerBootstrap();
        $envelope = $this->getEnvelope($message);

        $envelope = $events->handle($envelope, function () {
            throw new RuntimeException('bar');
        });

        $this->assertInstanceOf(
            RuntimeException::class,
            $envelope->currentActionEvent()->exception()
        );
    }

    protected function getEnvelope($message): Envelope
    {
        $tracker = new DefaultTracker([
            new DispatchedEvent(), new FinalizedEvent()
        ]);

        return new Envelope($message, $tracker);
    }
}