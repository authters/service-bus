<?php

namespace Authters\ServiceBus\Envelope\Route;

use Authters\ServiceBus\CommandBus;
use Authters\ServiceBus\Envelope\Envelope;

final class CommandRoute extends Route
{
    protected function processMessageHandler(Envelope $envelope, callable $messageHandler): Envelope
    {
        $messageHandler($envelope->getMessage());

        return $envelope;
    }

    protected function supportBus(Envelope $envelope): bool
    {
        return $envelope->isBusType(CommandBus::class);
    }
}