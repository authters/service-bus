<?php

namespace Authters\ServiceBus\Envelope\Route\Handler;

use Authters\ServiceBus\Exception\RuntimeException;

class FindHandler
{
    public function __invoke($messageHandler): callable
    {
        $method = 'find';
        $className = \get_class($messageHandler);

        if (!is_callable([$messageHandler, $method])) {
            throw new RuntimeException("Missing method {$method} in class $className");
        }

        return \Closure::fromCallable([$messageHandler, $method]);
    }
}