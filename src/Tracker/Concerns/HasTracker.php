<?php

namespace Authters\ServiceBus\Tracker\Concerns;

use Authters\ServiceBus\Contract\Tracker\ActionEvent;
use Authters\ServiceBus\Contract\Tracker\ListenerHandler;

trait HasTracker
{
    public function emit(ActionEvent $event): void
    {
        $this->dispatchEvent($event);
    }

    public function emitUntil(ActionEvent $event, callable $callback): void
    {
        $this->dispatchEvent($event, $callback);
    }

    private function dispatchEvent(ActionEvent $event, callable $callback = null): void
    {
        /** @var ListenerHandler $listenerHandler */
        foreach ($this->listeners($event) as $listenerHandler) {
            ($listenerHandler->getListener())($event);

            if ($event->isPropagationStopped()) {
                return;
            }

            if ($callback && true === $callback($event)) {
                return;
            }
        }
    }

    private function listeners(ActionEvent $event): iterable
    {
        $prioritizedListeners = $this->events[$event->getName()] ?? [];

        krsort($prioritizedListeners, SORT_NUMERIC);

        foreach ($prioritizedListeners as $listenersByPriority) {
            foreach ($listenersByPriority as $listenerHandler) {
                yield $listenerHandler;
            }
        }
    }
}