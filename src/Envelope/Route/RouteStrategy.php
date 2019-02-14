<?php

namespace Authters\ServiceBus\Envelope\Route;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Contract\Envelope\Route\Strategy\MessageRouteStrategy;
use Authters\ServiceBus\Envelope\Envelope;
use Prooph\Common\Messaging\Message;

class RouteStrategy implements Middleware
{
    /**
     * @var MessageRouteStrategy
     */
    private $strategy;

    public function __construct(MessageRouteStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function handle(Envelope $envelope, callable $next)
    {
        if ($envelope->getMessage() instanceof Message) {
            $message = $this->markProducerStrategyMetadata($envelope->getMessage());

            if ($asyncMessageMarked = $this->strategy->shouldBeDeferred($message)) {
                $envelope = $envelope->wrap($asyncMessageMarked);

                $envelope->markMessageReceived();

                return $envelope;
            }
        }

        return $next($envelope);
    }

    private function markProducerStrategyMetadata(Message $message): Message
    {
        $strategyMetadataKey = MessageRouteStrategy::ROUTE_STRATEGY_METADATA_KEY;

        if (null === (($message->metadata()[$strategyMetadataKey] ?? null))) {
            $message = $message->withAddedMetadata(
                $strategyMetadataKey, $this->strategy->strategyName()
            );
        }

        return $message;
    }
}