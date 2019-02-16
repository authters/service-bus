<?php

namespace Authters\ServiceBus\Message\Async;

use Authters\ServiceBus\CommandBus;
use Authters\ServiceBus\Contract\Message\MessageProducer;
use Authters\ServiceBus\EventBus;
use Authters\ServiceBus\Exception\RuntimeException;
use Authters\ServiceBus\Message\Job\MessageJob;
use Authters\ServiceBus\QueryBus;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageConverter;
use Prooph\Common\Messaging\Query;

final class IlluminateProducer implements MessageProducer
{
    /**
     * @var QueueingDispatcher
     */
    private $dispatcher;

    /**
     * @var MessageConverter
     */
    private $messageConverter;

    public function __construct(QueueingDispatcher $dispatcher, MessageConverter $messageConverter)
    {
        $this->dispatcher = $dispatcher;
        $this->messageConverter = $messageConverter;
    }

    public function __invoke(Message $message): void
    {
        $this->dispatcher->dispatchToQueue($this->toMessageJob($message));
    }

    protected function toMessageJob(Message $message): MessageJob
    {
        $payload = $this->messageConverter->convertToArray($message);

        return new MessageJob($payload, $this->determineBusType($message));
    }

    protected function determineBusType(Message $message): string
    {
        switch ($message) {
            case  $message instanceof Command:
                return CommandBus::class;
            case  $message instanceof Query:
                return QueryBus::class;
            case  $message instanceof DomainEvent:
                return EventBus::class;
        }

        throw new RuntimeException("Unknown bus type for message \get_class($message)");
    }
}