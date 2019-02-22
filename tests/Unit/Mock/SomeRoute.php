<?php

namespace AuthtersTest\ServiceBus\Unit\Mock;

use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Envelope\Route\Route;

class SomeRoute extends Route
{
    protected function processMessageHandler(Envelope $envelope, callable $messageHandler): Envelope
    {
        $messageHandler($envelope->getMessage());

        return $envelope;
    }

    protected function supportBus(Envelope $envelope): bool
    {
        return true;
    }
}