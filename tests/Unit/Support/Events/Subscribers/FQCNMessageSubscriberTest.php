<?php

namespace AuthtersTest\ServiceBus\Unit\Support\Events\Subscribers;

use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\ServiceBus\Support\Events\Subscriber\FQCNMessageSubscriber;
use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\DefaultActionEvent;
use AuthtersTest\ServiceBus\TestCase;
use AuthtersTest\ServiceBus\Unit\Mock\SomeCommand;
use Prooph\Common\Messaging\FQCNMessageFactory;

class FQCNMessageSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_transform_array_message_into_message_instance(): void
    {
        $sub = new FQCNMessageSubscriber(
            new FQCNMessageFactory()
        );

        $message = new SomeCommand(['foo' => 'bar']);

        $ev = $this->actionEvent($message->toArray());

        $sub->applyTo()($ev);

        $this->assertEquals($message, $ev->message());
        $this->assertEquals(\get_class($message), $ev->messageName());
    }

    /**
     * @test
     */
    public function it_transform_array_if_message_contains_message_name_key(): void
    {
        $sub = new FQCNMessageSubscriber(
            new FQCNMessageFactory()
        );

        $message = ['foo' => 'bar'];
        $ev = $this->actionEvent($message);

        $sub->applyTo()($ev);

        $this->assertEquals($message, $ev->message());
    }

    protected function actionEvent($message): ActionEvent
    {
        return new DefaultActionEvent(new DispatchedEvent(), function (ActionEvent $event) use ($message) {
            $event->setMessage($message);
        });
    }
}