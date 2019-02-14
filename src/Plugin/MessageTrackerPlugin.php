<?php

namespace Authters\ServiceBus\Plugin;

use Authters\ServiceBus\Contract\Plugin\Plugin;
use Authters\ServiceBus\Contract\Tracker\Tracker;

abstract class MessageTrackerPlugin implements Plugin
{
    /**
     * @var array
     */
    protected $listenerHandlers = [];

    public function unTrack(Tracker $tracker): void
    {
        foreach ($this->listenerHandlers as $listenerHandler) {
            $tracker->unsubscribe($listenerHandler);
        }

        $this->listenerHandlers = [];
    }
}