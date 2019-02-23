<?php

namespace Authters\ServiceBus\Support\Events\Subscriber;

use Authters\ServiceBus\Support\DetectMessageName;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\Tracker\Contract\MessageActionEvent;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class InitializeSubscriber extends AbstractSubscriber
{
    use DetectMessageName;

    public function applyTo(): callable
    {
        return function (MessageActionEvent $event) {
            $event->setMessageHandled(false);

            $event->setMessageName($this->detectMessageName($event->message()));
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