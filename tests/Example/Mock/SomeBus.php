<?php

namespace AuthtersTest\ServiceBus\Example\Mock;

use Authters\ServiceBus\Messager;
use Authters\ServiceBus\Support\TypedBus;

class SomeBus extends Messager
{
    use TypedBus;

    public function dispatch($message)
    {
        return $this->dispatchForBus(static::class, $message);
    }
}