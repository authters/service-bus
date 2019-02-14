<?php

namespace Authters\ServiceBus\Contract\Envelope\Route\Strategy;

use Prooph\Common\Messaging\Message;

interface MessageRouteStrategy
{
    public const ASYNC_METADATA_KEY = 'handled-async';

    public const ROUTE_ALL_ASYNC = 'route_all_async';

    public const ROUTE_NONE_ASYNC = 'route_none_async';

    public const ROUTE_ONLY_ASYNC = 'route_only_async';

    public const ROUTE_STRATEGY_METADATA_KEY = 'route_strategy';

    public function shouldBeDeferred(Message $message): ?Message;

    public function strategyName(): string;
}