<?php

namespace Authters\ServiceBus\Envelope\Route;

use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Exception\MessageDispatchedFailure;
use Authters\ServiceBus\QueryBus;
use React\Promise\Deferred;

final class QueryRoute extends Route
{
    protected function processMessageHandler(Envelope $envelope, callable $messageHandler): Envelope
    {
        $deferred = new Deferred();

        try {
            $messageHandler($envelope->getMessage(), $deferred);
        } catch (\Throwable $exception) {
            $deferred->reject(MessageDispatchedFailure::reason($exception));
        } finally {
            $envelope->addContent($deferred->promise());
        }

        return $envelope;
    }

    protected function supportBus(Envelope $envelope): bool
    {
        return $envelope->isBusType(QueryBus::class);
    }
}