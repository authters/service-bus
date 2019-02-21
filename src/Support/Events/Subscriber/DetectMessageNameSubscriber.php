<?php

namespace Authters\ServiceBus\Support\Events\Subscriber;

use Authters\ServiceBus\Support\DetectMessageName;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class DetectMessageNameSubscriber extends AbstractSubscriber
{
    use DetectMessageName;

    public function applyTo(): callable
    {
        return function (ActionEvent $event) {
            $event->setMessageName(
                $this->detectMessageName($event->message())
            );
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new DispatchedEvent();
    }

    public function priority(): int
    {
        return 1000;
    }
}