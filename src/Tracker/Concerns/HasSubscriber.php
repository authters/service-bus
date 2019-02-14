<?php

namespace Authters\ServiceBus\Tracker\Concerns;

use Authters\ServiceBus\Contract\Tracker\ListenerHandler;
use Authters\ServiceBus\Tracker\DefaultListenerHandler;

trait HasSubscriber
{
    public function subscribe(string $event, callable $callback, int $priority = 0): ListenerHandler
    {
        if (!in_array($event, $this->eventNames, true)) {
            throw new \InvalidArgumentException("Unknown event name: $event");
        }

        $handler = new DefaultListenerHandler($callback);

        $this->events[$event][((int)$priority) . '.0'][] = $handler;

        return $handler;
    }

    public function unsubscribe(ListenerHandler $handler): bool
    {
        foreach ($this->events as &$prioritizedListeners) {
            foreach ($prioritizedListeners as &$listenerHandlers) {
                foreach ($listenerHandlers as $index => $listedListenerHandler) {
                    if ($listedListenerHandler === $handler) {
                        unset($listenerHandlers[$index]);

                        return true;
                    }
                }
            }
        }

        return false;
    }
}