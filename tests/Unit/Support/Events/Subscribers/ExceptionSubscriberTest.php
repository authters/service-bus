<?php

namespace AuthtersTest\ServiceBus\Unit\Support\Events\Subscribers;

use Authters\ServiceBus\Support\Events\Named\FinalizedEvent;
use Authters\ServiceBus\Support\Events\Subscriber\ExceptionSubscriber;
use Authters\Tracker\Contract\MessageActionEvent;
use Authters\Tracker\DefaultActionEvent;
use AuthtersTest\ServiceBus\TestCase;

class ExceptionSubscriberTest extends TestCase
{
    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function it_raise_exception(): void
    {
        $sub = new ExceptionSubscriber();

        $exception = new \RuntimeException('foo');

        $event = new DefaultActionEvent(new FinalizedEvent(), function (MessageActionEvent $event) use ($exception) {
            $event->setException($exception);
        });

        $this->expectExceptionMessage('foo');
        $sub->applyTo()($event);
    }
}