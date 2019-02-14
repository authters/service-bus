<?php

namespace AuthtersTest\ServiceBus\Unit\Mock;

use React\Promise\Deferred;

class SomeQueryHandler
{
    public function __invoke($message, Deferred $deferred): void
    {
        $deferred->resolve($message);
    }
}