<?php

namespace Authters\ServiceBus;

use Authters\ServiceBus\Support\TypedBus;
use React\Promise\PromiseInterface;

class QueryBus extends Messager
{
    use TypedBus;

    public function dispatch($message): PromiseInterface
    {
       return $this->dispatchForBus(static::class, $message);
    }
}