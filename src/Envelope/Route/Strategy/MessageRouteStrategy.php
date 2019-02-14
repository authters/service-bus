<?php

namespace Authters\ServiceBus\Envelope\Route\Strategy;

use Authters\ServiceBus\Contract\Envelope\Route\Strategy\MessageRouteStrategy as BaseStrategy;
use Authters\ServiceBus\Contract\Message\MessageProducer;
use Authters\ServiceBus\Exception\RuntimeException;
use Prooph\Common\Messaging\Message;

abstract class MessageRouteStrategy implements BaseStrategy
{
    /**
     * @var MessageProducer
     */
    private $messageProducer;

    public function __construct(MessageProducer $messageProducer = null)
    {
        $this->messageProducer = $messageProducer;
    }

    public function shouldBeDeferred(Message $message): ?Message
    {
        if ($asyncMessageMarked = $this->mustBeProducedAsync($message)) {
            if (!$this->messageProducer) {
                $exceptionMessage = "A message producer is missing for strategy {$this->strategyName()}";
                throw new RuntimeException($exceptionMessage);
            }

            ($this->messageProducer)($asyncMessageMarked);

            return $asyncMessageMarked;
        }

        return null;
    }

    protected function markAsync(Message $message): ?Message
    {
        if ($this->isNotPreviouslyMarkedAsync($message)) {
            $message = $message->withAddedMetadata(self::ASYNC_METADATA_KEY, true);

            return $message;
        }

        return null;
    }

    protected function isNotPreviouslyMarkedAsync(Message $message): bool
    {
        return false === ($message->metadata()[self::ASYNC_METADATA_KEY] ?? false);
    }

    abstract protected function mustBeProducedAsync(Message $message): ?Message;
}