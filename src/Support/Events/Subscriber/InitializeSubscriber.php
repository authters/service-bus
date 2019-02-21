<?php

namespace Authters\ServiceBus\Support\Events\Subscriber;

use Authters\ServiceBus\Support\DetectMessageName;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\Contract\NamedEvent;

class InitializeSubscriber
{
    use DetectMessageName;

    public function priority(): int
    {
        return 40000;
    }

    public function subscribeTo(): NamedEvent
    {
        return new DispatchedEvent();
    }

    public function applyTo(): callable
    {
        return function (ActionEvent $event) {
            $event->setMessageHandled(false);

            $event->setMessageName($this->detectMessageName($event->message()));
        };
    }
}