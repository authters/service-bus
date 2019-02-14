<?php

namespace Authters\ServiceBus\Envelope\Bootstrap;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Envelope\Envelope;

final class MessageTrackerBootstrap implements Middleware
{
    public function handle(Envelope $envelope, callable $next)
    {
        $message = $envelope->getMessage();

        $event = $envelope->newActionEvent($this, function (MessageActionEvent $event) use ($message) {
            $event->setMessage($message);
        });

        try {
            $envelope->dispatching($event);

            $envelope = $next($envelope);
        } catch (\Throwable $exception) {
            $event->setException($exception);
        } finally {
            $event->stopPropagation(true);

            $envelope->finalizing($event);
        }

        return $envelope;
    }
}