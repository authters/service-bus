<?php

namespace AuthtersTest\ServiceBus\Unit\Support\Events\Subscribers;

use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\ServiceBus\Support\Events\Subscriber\InitializeSubscriber;
use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\DefaultActionEvent;
use AuthtersTest\ServiceBus\TestCase;
use AuthtersTest\ServiceBus\Unit\Mock\SomeCommand;

class InitializeSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_initialize_action_event(): void
    {
        $sub = new InitializeSubscriber();

        $message = new SomeCommand(['foo'=> 'bar']);

        $event = $this->getMockForAbstractClass(ActionEvent::class);
        $event->expects($this->once())->method('setMessageHandled');
        $event->expects($this->exactly(2))->method('message')->willReturn($message);
        $event->expects($this->once())->method('setMessageName')->willReturn('foo');

        $sub->applyTo()($event);

        $this->assertEquals($message, $event->message());
    }
}