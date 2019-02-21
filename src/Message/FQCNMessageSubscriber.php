<?php

namespace Authters\ServiceBus\Message;

use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Contract\SubscribedEvent;
use Authters\Tracker\Event\Named\OnDispatched;
use Prooph\Common\Messaging\FQCNMessageFactory;

final class FQCNMessageSubscriber implements SubscribedEvent
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
        return function (ActionEvent $event) {
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

    public function priority(): int
    {
        return 40000;
    }

    public function subscribeTo(): NamedEvent
    {
        return new OnDispatched();
    }
}