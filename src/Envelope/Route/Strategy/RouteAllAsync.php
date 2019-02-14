<?php

namespace Authters\ServiceBus\Envelope\Route\Strategy;

use Prooph\Common\Messaging\Message;

class RouteAllAsync extends MessageRouteStrategy
{
    public function mustBeProducedAsync(Message $message): ?Message
    {
        return $this->markAsync($message) ?? null;
    }

    public function strategyName(): string
    {
        return self::ROUTE_ALL_ASYNC;
    }
}