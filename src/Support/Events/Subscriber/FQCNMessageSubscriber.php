<?php

namespace Authters\ServiceBus\Support\Events\Subscriber;

use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\Tracker\Contract\MessageActionEvent;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;
use Prooph\Common\Messaging\FQCNMessageFactory;

class FQCNMessageSubscriber extends AbstractSubscriber
{
    /**
     * @var FQCNMessageFactory
     */
    private $messageFactory;

    public function __construct(FQCNMessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    public function applyTo(): callable
    {
        return function (MessageActionEvent $event) {
            $message = $event->message();

            if (\is_array($message) && array_key_exists('message_name', $message)) {
                $messageName = $message['message_name'];
                unset($message['message_name']);

                $convertedMessage = $this->messageFactory->createMessageFromArray(
                    $messageName,
                    $message
                );

                $event->setMessage($convertedMessage);
                $event->setMessageName($messageName);
            }
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new DispatchedEvent();
    }

    public function priority(): int
    {
        return 40000;
    }
}