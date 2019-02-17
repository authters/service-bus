<?php

namespace Authters\ServiceBus\Contract\Tracker;

interface EventSubscriber
{
    public function attachToTracker(Tracker $tracker, string $messageBus): void;

    public function detachFromTracker(Tracker $tracker): void;
}