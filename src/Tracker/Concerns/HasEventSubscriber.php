<?php

namespace Authters\ServiceBus\Tracker\Concerns;

use Authters\ServiceBus\Contract\Tracker\Tracker;

trait HasEventSubscriber
{
    /**
     * @var array
     */
    protected $listenerHandlers = [];

    public function detachToBus(Tracker $tracker): void
    {
        foreach ($this->listenerHandlers as $listenerHandler) {
            $tracker->unsubscribe($listenerHandler);
        }

        $this->listenerHandlers = [];
    }
}