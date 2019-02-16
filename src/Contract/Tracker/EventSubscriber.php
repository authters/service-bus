<?php

namespace Authters\ServiceBus\Contract\Tracker;

interface EventSubscriber
{
    public function attachToBus(Tracker $tracker, string $messageBus): void;

    public function detachToBus(Tracker $tracker): void;
}