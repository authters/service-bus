<?php

namespace Authters\ServiceBus\Envelope\Bootstrap;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\Event\Named\OnDispatched;
use Authters\Tracker\Event\Named\OnFinalized;

final class MessageTrackerBootstrap implements Middleware
{
    public function handle(Envelope $envelope, callable $next)
    {
        $event = $this->createActionEvent($envelope);

        try {
            $event->setEvent(new OnDispatched());
            $envelope->getTracker()->emit($event);

            $envelope = $next($envelope);
        } catch (\Throwable $exception) {
            $event->setException($exception);
        } finally {
            $event->stopPropagation(true);

            $event->setEvent(new OnFinalized());

            $envelope->getTracker()->emit($event);
        }

        return $envelope;
    }

    private function createActionEvent(Envelope $envelope): ActionEvent
    {
        $message = $envelope->getMessage();

        return $envelope->newActionEvent($this, function (ActionEvent $event) use ($message) {
            $event->setMessage($message);
        });
    }
}