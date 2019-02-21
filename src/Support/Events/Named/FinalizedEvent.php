<?php

namespace Authters\ServiceBus\Support\Events\Named;

use Authters\Tracker\Event\AbstractNamedEvent;

class FinalizedEvent extends AbstractNamedEvent
{
    public function name(): string
    {
        return 'finalize';
    }

    public function priority(): int
    {
        return 0;
    }
}