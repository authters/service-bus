<?php

namespace Authters\ServiceBus\Tracker\Concerns;

use Authters\ServiceBus\Contract\Tracker\ActionEvent;
use Authters\ServiceBus\Contract\Tracker\ListenerHandler;
use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Exception\MessageDispatchedFailure;
use Authters\ServiceBus\Tracker\DefaultActionEvent;

trait HasEventTracker
{
    public function createEvent(string $name, $target = null, callable $attributes = null): ActionEvent
    {
        return new DefaultActionEvent($name ?? self::ACTION_EVENT_NAME, $target, $attributes);
    }

    public function listenToDispatcher(callable $callback, int $priority = 1): ListenerHandler
    {
        return $this->subscribe(self::EVENT_DISPATCH, $callback, $priority);
    }

    public function listenToFinalizer(callable $callback, int $priority = 1): ListenerHandler
    {
        return $this->subscribe(self::EVENT_FINALIZE, $callback, $priority);
    }

    protected function attachToTracker(): void
    {
        $this->listenToDispatcher($this->onInitialization(), self::PRIORITY_INITIALIZE);

        $this->listenToDispatcher($this->onDetectMessageName(), self::PRIORITY_DETECT_MESSAGE_NAME);

        $this->listenToFinalizer($this->onException(), 1);
    }

    private function onInitialization(): callable
    {
        return function (DefaultActionEvent $event) {
            $event->setMessageHandled(false);

            $messageName = $this->detectMessageName($event->message());

            $event->setMessageName($messageName);
        };
    }

    private function onDetectMessageName(): callable
    {
        return function (MessageActionEvent $event) {
            $messageName = $this->detectMessageName($event->message());

            $event->setMessageName($messageName);
        };
    }

    private function onException(): callable
    {
        return function (MessageActionEvent $event) {
            if ($event->hasException()) {
                throw MessageDispatchedFailure::reason($event->exception());
            }
        };
    }
}