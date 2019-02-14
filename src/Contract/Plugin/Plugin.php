<?php

namespace Authters\ServiceBus\Contract\Plugin;

use Authters\ServiceBus\Contract\Tracker\Tracker;

interface Plugin
{
    public function track(Tracker $tracker): void;

    public function unTrack(Tracker $tracker): void;
}