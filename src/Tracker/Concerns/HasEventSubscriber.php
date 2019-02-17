<?php

namespace Authters\ServiceBus\Tracker\Concerns;

use Authters\ServiceBus\Contract\Tracker\Tracker;

trait HasEventSubscriber
{
    /**
     * @var array
     */
    protected $listenerHandlers = [];

    public function detachFromTracker(Tracker $tracker): void
    {
        foreach ($this->listenerHandlers as $listenerHandler) {
            $tracker->unsubscribe($listenerHandler);
        }

        $this->listenerHandlers = [];
    }
}