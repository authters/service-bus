<?php

namespace AuthtersTest\ServiceBus\Unit\Support\Events\Subscribers;

use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\ServiceBus\Support\Events\Subscriber\DetectMessageNameSubscriber;
use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\DefaultActionEvent;
use AuthtersTest\ServiceBus\TestCase;
use AuthtersTest\ServiceBus\Unit\Mock\SomeCommand;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\PayloadTrait;


class DetectMessageNameSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_named_message_fom_string(): void
    {
        $sub = new DetectMessageNameSubscriber();
        $message = 'foo';
        $event = $this->actionEvent($message);

        $sub->applyTo()($event);
        $this->assertEquals($message, $event->messageName());

    }

    /**
     * @test
     */
    public function it_named_message_from_object(): void
    {
        $sub = new DetectMessageNameSubscriber();
        $message = new SomeCommand([]);

        $event = $this->actionEvent($message);
        $sub->applyTo()($event);
        $this->assertEquals(\get_class($message), $event->messageName());
    }

    /**
     * @test
     */
    public function it_named_message_from_array(): void
    {
        $sub = new DetectMessageNameSubscriber();
        $message = ['foo' => 'bar'];
        $event = $this->actionEvent($message);

        $sub->applyTo()($event);
        $this->assertEquals('array', $event->messageName());
    }

    /**
     * @test
     */
    public function it_named_message_from_message_instance(): void
    {
        $sub = new DetectMessageNameSubscriber();
        $message = $this->someMessage();
        $event = $this->actionEvent($message);

        $sub->applyTo()($event);
        $this->assertEquals('foo_bar', $event->messageName());
    }

    protected function someMessage(): Message
    {
        return new class() extends Command
        {
            use PayloadTrait;

            protected $messageName = 'foo_bar';
        };
    }

    protected function actionEvent($message): DefaultActionEvent
    {
        return new DefaultActionEvent(new DispatchedEvent(), function (ActionEvent $event) use ($message) {
            $event->setMessage($message);
        });
    }
}