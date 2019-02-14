<?php

namespace Authters\ServiceBus\Envelope\Route\Strategy;

use Prooph\Common\Messaging\Message;

class RouteNoneAsync extends MessageRouteStrategy
{
    protected function mustBeProducedAsync(Message $message): ?Message
    {
       return null;
    }

    public function strategyName(): string
    {
        return self::ROUTE_NONE_ASYNC;
    }
}