<?php

namespace Authters\ServiceBus\Contract\Tracker;

interface MessageTracker extends Tracker
{
    public function initialize(MessageActionEvent $event): void;

    public function finalize(MessageActionEvent $event): void;

    public function ListenToDispatcher(callable $callback, int $priority = 1): ListenerHandler;

    public function ListenToFinalizer(callable $callback, int $priority = 1): ListenerHandler;
}