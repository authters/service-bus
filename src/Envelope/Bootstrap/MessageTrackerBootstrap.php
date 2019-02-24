<?php

namespace Authters\ServiceBus\Envelope\Bootstrap;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Support\Events\Named\FinalizedEvent;
use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\Contract\MessageActionEvent;

final class MessageTrackerBootstrap implements Middleware
{
    public function handle(Envelope $envelope, callable $next)
    {
        $event = $this->createDispatchedActionEvent($envelope);

        try {
            $envelope->getTracker()->emit($event);

            $envelope = $next($envelope);
        } catch (\Throwable $exception) {
            $event->setException($exception);
        } finally {
            $event->stopPropagation(false);

            $event->setEvent(new FinalizedEvent($this));

            $envelope->getTracker()->emit($event);
        }

        return $envelope;
    }

    private function createDispatchedActionEvent(Envelope $envelope): ActionEvent
    {
        $message = $envelope->getMessage();

        return $envelope->newActionEvent($this, function (MessageActionEvent $event) use ($message) {
            $event->setMessage($message);
        });
    }
}