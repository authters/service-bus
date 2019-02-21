<?php

namespace Authters\ServiceBus\Envelope;

use Authters\ServiceBus\Exception\RuntimeException;
use Authters\ServiceBus\Support\DetectMessageName;
use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\Contract\Tracker;
use Authters\Tracker\Event\Named\OnDispatched;

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
     * @var \Authters\Tracker\Contract\Tracker
     */
    private $tracker;

    /**
     * @var ActionEvent
     */
    private $actionEvent;

    public function __construct($message, Tracker $tracker)
    {
        $this->message = $message;
        $this->tracker = $tracker;
    }

    public function getMessage()
    {
        if ($this->actionEvent) {
            return $this->actionEvent->message();
        }

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
        $this->actionEvent = $this->tracker->newActionEvent(new OnDispatched($target), $callback);

        return $this->actionEvent;
    }

    public function currentActionEvent(): ActionEvent
    {
        return $this->actionEvent;
    }

    public function wrap($message): self
    {
        if ($message instanceof self) {
            return clone $message;
        }

        $envelope = new self($message, $this->tracker);
        $envelope->busType = $this->busType;
        $envelope->content = $this->content;

        if ($this->actionEvent) {
            $envelope->actionEvent = $this->actionEvent;
            $envelope->actionEvent->setMessageName($this->detectMessageName($message));
        }

        return $envelope;
    }

    public function getTracker(): Tracker
    {
        return $this->tracker;
    }
}