<?php

namespace Authters\ServiceBus\Support\Events\Subscriber;

use Authters\ServiceBus\Support\Events\Named\FinalizedEvent;
use Authters\Tracker\Contract\MessageActionEvent;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class ExceptionSubscriber extends AbstractSubscriber
{
    public function priority(): int
    {
        return 40000;
    }

    public function subscribeTo(): NamedEvent
    {
        return new FinalizedEvent();
    }

    public function applyTo(): callable
    {
        return function (MessageActionEvent $event) {
            if ($exception = $event->exception()) {
                throw $exception;
            }
        };
    }
}