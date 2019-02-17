<?php

namespace Authters\ServiceBus;

class EventBus extends Messager
{
    public function dispatch($message): void
    {
        $this->dispatchForBus(static::class, $message);
    }
}