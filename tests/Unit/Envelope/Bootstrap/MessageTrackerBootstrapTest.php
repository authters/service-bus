<?php

namespace AuthtersTest\ServiceBus\Unit\Envelope\Bootstrap;

use Authters\ServiceBus\Contract\Tracker\ActionEvent;
use Authters\ServiceBus\Contract\Tracker\Tracker;
use Authters\ServiceBus\Envelope\Bootstrap\MessageTrackerBootstrap;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Exception\RuntimeException;
use Authters\ServiceBus\Tracker\DefaultMessageTracker;
use AuthtersTest\ServiceBus\TestCase;

class MessageTrackerBootstrapTest extends TestCase
{
    /**
     * @test
     */
    public function it_initialize_message_event(): void
    {
        $message = 'foo';
        $events = new MessageTrackerBootstrap();
        $tracker = new DefaultMessageTracker();

        $listener = $tracker->listenToDispatcher(function (ActionEvent $event) {
            $this->assertFalse($event->isPropagationStopped());
            $this->assertEquals(Tracker::EVENT_DISPATCH, $event->getName());
        });

        $envelope = new Envelope($message, $tracker);

        $envelopeDispatched = $events->handle($envelope, function () use ($envelope, $listener) {
            $listener->getListener()($envelope->currentActionEvent());

            return $envelope;
        });

        $this->assertEquals($message, $envelopeDispatched->getMessage());
    }

    /**
     * @test
     */
    public function it_finalize_message_event(): void
    {
        $message = 'foo';
        $events = new MessageTrackerBootstrap();
        $tracker = new DefaultMessageTracker();

        $listener = $tracker->listenToFinalizer(function (ActionEvent $event) {
            $this->assertTrue($event->isPropagationStopped());
            $this->assertEquals(Tracker::EVENT_FINALIZE, $event->getName());
        });

        $envelope = new Envelope($message, $tracker);

        $envelopeFinalized = $events->handle($envelope, function () use ($envelope) {
            return $envelope;
        });

        $this->assertEquals($message, $envelopeFinalized->getMessage());

        $listener->getListener()($envelope->currentActionEvent());
    }

    /**
     * @test
     * @expectedException \Authters\ServiceBus\Exception\MessageDispatchedFailure
     */
    public function it_transform_exception_caught_during_dispatching(): void
    {
        $message = 'foo';
        $events = new MessageTrackerBootstrap();
        $envelope = new Envelope($message, new DefaultMessageTracker());

        try {
            $events->handle($envelope, function () {
                throw new RuntimeException('bar');
            });
        } catch (\Throwable $exceptions) {
            $this->assertTrue($envelope->currentActionEvent()->isPropagationStopped());
            $this->assertEquals(RuntimeException::class, \get_class($exceptions->getPrevious()));
            $this->assertEquals('bar', $exceptions->getPrevious()->getMessage());
            throw $exceptions;
        }
    }
}