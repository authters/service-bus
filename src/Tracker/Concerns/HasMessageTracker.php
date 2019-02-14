<?php

namespace Authters\ServiceBus\Tracker\Concerns;

use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;

trait HasMessageTracker
{
    public function initialize(MessageActionEvent $event): void
    {
        $this->emit($event);
    }

    public function finalize(MessageActionEvent $event): void
    {
        $event->setName(self::EVENT_FINALIZE);

        $this->emit($event);
    }

    protected function getEventNames(): array
    {
        return [self::EVENT_DISPATCH, self::EVENT_FINALIZE];
    }
}