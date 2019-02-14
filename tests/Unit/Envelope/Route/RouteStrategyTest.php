<?php

namespace AuthtersTest\ServiceBus\Unit\Envelope\Route;

use Authters\ServiceBus\Contract\Envelope\Route\Strategy\MessageRouteStrategy;
use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Envelope\Route\RouteStrategy;
use Authters\ServiceBus\Tracker\MessageTracker;
use AuthtersTest\ServiceBus\TestCase;
use AuthtersTest\ServiceBus\Unit\Mock\SomeCommand;
use Prooph\Common\Messaging\Message;

class RouteStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_mark_metadata_message_with_async_strategy(): void
    {
        $message = new SomeCommand(['foo' => 'bar']);
        $asyncMessage = $this->markAsyncMessage($message);
        $this->strategy->expects($this->once())->method('shouldBeDeferred')->willReturn($asyncMessage);

        $instance = $this->getRouteStrategyInstance();

        $envelope = $this->dispatchWithMessage($message);
        $envelopeMarked = $instance->handle($envelope, function () use ($envelope) {
            return $envelope;
        });

        $this->assertArrayHasKey(MessageRouteStrategy::ASYNC_METADATA_KEY, $asyncMessage->metadata());
        $this->assertTrue($asyncMessage->metadata()[MessageRouteStrategy::ASYNC_METADATA_KEY]);
        $this->assertTrue($envelope->hasReceipt());

        $this->assertEquals($asyncMessage, $envelopeMarked->getMessage());
    }

    protected function dispatchWithMessage(Message $message): Envelope
    {
        $envelope = new Envelope($message, new MessageTracker());
        $event = $envelope->newActionEvent($this, function (MessageActionEvent $event) use ($message) {
            $event->setMessage($message);
        });

        $envelope->dispatching($event);

        return $envelope;
    }

    protected function getRouteStrategyInstance(): RouteStrategy
    {
        return new RouteStrategy($this->strategy);
    }

    protected function markAsyncMessage(Message $message): Message
    {
        return $message->withAddedMetadata(MessageRouteStrategy::ASYNC_METADATA_KEY, true);
    }

    private $strategy;
    public function setUp(): void
    {
        $this->strategy = $this->getMockForAbstractClass(MessageRouteStrategy::class);
    }
}