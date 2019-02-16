<?php

namespace Authters\ServiceBus\Envelope\Bootstrap;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Contract\Tracker\ActionEvent;
use Authters\ServiceBus\Contract\Tracker\EventSubscriber;
use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Envelope\Envelope;

final class MessageTrackerBootstrap implements Middleware
{
    /**
     * @var array
     */
    private $subscribers;

    public function __construct(array $subscribers = [])
    {
        $this->subscribers = $subscribers;
    }

    public function handle(Envelope $envelope, callable $next)
    {
        $event = $this->createActionEvent($envelope);

        $this->attachToTracker($envelope);

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

    private function createActionEvent(Envelope $envelope): ActionEvent
    {
        $message = $envelope->getMessage();

        return $envelope->newActionEvent($this, function (MessageActionEvent $event) use ($message) {
            $event->setMessage($message);
        });
    }

    private function attachToTracker(Envelope $envelope): void
    {
        /** @var EventSubscriber $subscriber */
        foreach ($this->subscribers as $subscriber) {
            $subscriber->attachToBus($envelope->getMessageTracker(), $envelope->busType());
        }
    }
}