<?php

namespace Authters\ServiceBus\Contract\Tracker;

interface Tracker
{
    public const EVENT_DISPATCH = 'dispatch';
    public const EVENT_FINALIZE = 'finalize';
    public const PRIORITY_INITIALIZE = 400000;
    public const PRIORITY_DETECT_MESSAGE_NAME = 300000;
    public const PRIORITY_ROUTE = 200000;
    public const PRIORITY_LOCATE_HANDLER = 100000;
    public const PRIORITY_PROMISE_REJECT = 1000;
    public const PRIORITY_INVOKE_HANDLER = 0;

    public function createEvent(string $name, $target = null, callable $attributes = null): ActionEvent;

    public function emit(ActionEvent $event): void;

    public function emitUntil(ActionEvent $event, callable $callback): void;

    public function subscribe(string $event, callable $callback, int $priority = 0): ListenerHandler;

    public function unsubscribe(ListenerHandler $listenerHandler): bool;
}