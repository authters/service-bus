<?php

namespace Authters\ServiceBus\Support;

use Authters\ServiceBus\Envelope\Envelope;

trait TypedBus
{
    protected function dispatchForBus(string $busType, $message)
    {
        $envelope = new Envelope($message, $this->tracker);
        $envelope->setBusType($busType);

        return $this->dispatchMessage($envelope);
    }
}