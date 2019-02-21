<?php

namespace Authters\ServiceBus\Support\Events\Named;

use Authters\Tracker\Event\AbstractNamedEvent;

class DispatchedEvent extends AbstractNamedEvent
{
    public function name(): string
    {
        return 'dispatch';
    }

    public function priority(): int
    {
        return 30000;
    }
}