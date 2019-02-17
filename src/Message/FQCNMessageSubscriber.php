<?php

namespace Authters\ServiceBus\Message;

use Authters\ServiceBus\Contract\Tracker\EventSubscriber;
use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Contract\Tracker\Tracker;
use Authters\ServiceBus\Tracker\Concerns\HasEventSubscriber;
use Prooph\Common\Messaging\FQCNMessageFactory;

class FQCNMessageSubscriber implements EventSubscriber
{
    use HasEventSubscriber;

    /**
     * @var FQCNMessageFactory
     */
    private $messageFactory;

    public function __construct(FQCNMessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    public function attachToTracker(Tracker $tracker, string $messageBus): void
    {
        $this->listenerHandlers[] = $tracker->subscribe(Tracker::EVENT_DISPATCH, function (MessageActionEvent $event) {
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
        }, Tracker::PRIORITY_INITIALIZE);
    }
}