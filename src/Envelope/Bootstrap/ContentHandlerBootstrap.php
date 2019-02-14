<?php

namespace Authters\ServiceBus\Envelope\Bootstrap;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Envelope\Envelope;

final class ContentHandlerBootstrap implements Middleware
{
    public function handle(Envelope $envelope, callable $next)
    {
        /** @var Envelope $envelope */
        $envelope = $next($envelope);

        return $this->handleContent($envelope->getContent());
    }

    private function handleContent(array $results)
    {
        if (1 === \count($results)) {
            return array_shift($results);
        }

        return $results ?? null;
    }
}