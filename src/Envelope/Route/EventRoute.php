<?php

namespace Authters\ServiceBus\Envelope\Route;

use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\EventBus;

final class EventRoute extends Route
{
    protected function processMessageHandler(Envelope $envelope, callable $messageHandler): Envelope
    {
        $messageHandler($envelope->getMessage());

        return $envelope;
    }

    protected function supportBus(Envelope $envelope): bool
    {
        return $envelope->isBusType(EventBus::class);
    }
}