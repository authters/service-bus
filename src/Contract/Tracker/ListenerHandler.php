<?php

namespace Authters\ServiceBus\Contract\Tracker;

interface ListenerHandler
{
    public function getListener(): callable;
}