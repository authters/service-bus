<?php

namespace Authters\ServiceBus\Envelope\Route\Strategy;

use Authters\ServiceBus\Contract\Message\AsyncMessage;
use Prooph\Common\Messaging\Message;

class RouteOnlyMarkedAsync extends MessageRouteStrategy
{
    protected function mustBeProducedAsync(Message $message): ?Message
    {
        if ($message instanceof AsyncMessage && $asyncMessageMarked = $this->markAsync($message)) {
            return $asyncMessageMarked;
        }

        return null;
    }

    public function strategyName(): string
    {
        return self::ROUTE_ONLY_ASYNC;
    }
}