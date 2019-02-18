<?php

namespace Authters\ServiceBus\Tracker\Concerns;

use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Exception\MessageDispatchedFailure;

trait HasDefaultEvents
{
    protected function onInitialization(): void
    {
        $callable = function (MessageActionEvent $event) {
            $event->setMessageHandled(false);

            $messageName = $this->detectMessageName($event->message());

            $event->setMessageName($messageName);
        };

        $this->subscribe(self::EVENT_DISPATCH, $callable, self::PRIORITY_INITIALIZE);
    }

    protected function onDetectMessageName(): void
    {
        $callable = function (MessageActionEvent $event) {
            $messageName = $this->detectMessageName($event->message());

            $event->setMessageName($messageName);
        };

        $this->subscribe(self::EVENT_DISPATCH, $callable, self::PRIORITY_DETECT_MESSAGE_NAME);
    }

    protected function onException(): void
    {
        $callable = function (MessageActionEvent $event) {
            if ($event->hasException()) {
                throw MessageDispatchedFailure::reason($event->exception());
            }
        };

        $this->subscribe(self::EVENT_FINALIZE, $callable, 1);
    }
}