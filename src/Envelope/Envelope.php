<?php

namespace Authters\ServiceBus\Envelope;

use Authters\ServiceBus\Contract\Tracker\ActionEvent;
use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Contract\Tracker\MessageTracker;
use Authters\ServiceBus\Contract\Tracker\Tracker;
use Authters\ServiceBus\Exception\RuntimeException;
use Authters\ServiceBus\Support\DetectMessageName;

class Envelope
{
    use DetectMessageName;

    /**
     * @var mixed
     */
    private $message;

    /**
     * @var string
     */
    private $busType;

    /**
     * @var array
     */
    private $content = [];

    /**
     * @var MessageTracker
     */
    private $messageTracker;

    /**
     * @var MessageActionEvent
     */
    private $actionEvent;

    public function __construct($message, MessageTracker $messageTracker)
    {
        $this->message = $message;
        $this->messageTracker = $messageTracker;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function messageName(): string
    {
        return $this->actionEvent->messageName();
    }

    public function setBusType(string $busType): void
    {
        $this->busType = $busType;
    }

    public function isBusType(string $expectedBusType): bool
    {
        return $expectedBusType === $this->busType
            || is_subclass_of($this->busType, $expectedBusType);
    }

    public function busType(): string
    {
        if (!$this->busType) {
            throw new RuntimeException('Missing message bus type in envelope');
        }

        return $this->busType;
    }

    public function dispatching(MessageActionEvent $event): void
    {
        $this->messageTracker->initialize($event);

        $this->actionEvent = $event;
    }

    public function finalizing(MessageActionEvent $event): void
    {
        $this->messageTracker->finalize($event);

        $this->actionEvent = $event;
    }

    public function markMessageReceived(): void
    {
        $this->actionEvent->setMessageHandled(true);
    }

    public function hasReceipt(): bool
    {
        return $this->actionEvent->isMessageHandled();
    }

    public function addContent($content): void
    {
        $this->content[] = $content;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function newActionEvent($target = null, callable $callback = null): ActionEvent
    {
        return $this->messageTracker->createEvent('dispatch', $target, $callback);
    }

    public function currentActionEvent(): MessageActionEvent
    {
        return $this->actionEvent;
    }

    public function wrap($message): self
    {
        if ($message instanceof self) {
            return clone $message;
        }

        $envelope = new self($message, $this->messageTracker);
        $envelope->busType = $this->busType;
        $envelope->content = $this->content;

        $this->actionEvent->setMessageName($this->detectMessageName($message));
        $envelope->actionEvent = $this->actionEvent;

        return $envelope;
    }

    public function getMessageTracker(): Tracker
    {
        return $this->messageTracker;
    }
}