<?php

namespace Authters\ServiceBus;

use React\Promise\PromiseInterface;

class QueryBus extends Messager
{
    public function dispatch($message): PromiseInterface
    {
       return $this->dispatchForBus(static::class, $message);
    }
}