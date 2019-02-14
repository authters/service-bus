<?php

namespace Authters\ServiceBus;

use Authters\ServiceBus\Support\TypedBus;

class CommandBus extends Messager
{
    use TypedBus;

    public function dispatch($message): void
    {
       $this->dispatchForBus(static::class, $message);
    }
}