<?php

namespace Authters\ServiceBus;

class CommandBus extends Messager
{
    public function dispatch($message): void
    {
       $this->dispatchForBus(static::class, $message);
    }
}