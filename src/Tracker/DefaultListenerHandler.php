<?php

namespace Authters\ServiceBus\Tracker;

use Authters\ServiceBus\Contract\Tracker\ListenerHandler;

class DefaultListenerHandler implements ListenerHandler
{
    /**
     * @var callable
     */
    private $listener;

    public function __construct(callable $listener)
    {
        $this->listener = $listener;
    }

    public function getListener(): callable
    {
        return $this->listener;
    }
}