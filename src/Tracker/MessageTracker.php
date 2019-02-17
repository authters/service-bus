<?php

namespace Authters\ServiceBus\Tracker;

use Authters\ServiceBus\Contract\Tracker\ActionEvent;
use Authters\ServiceBus\Contract\Tracker\ListenerHandler;
use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Contract\Tracker\MessageTracker as BaseBusTracker;
use Authters\ServiceBus\Support\DetectMessageName;
use Authters\ServiceBus\Tracker\Concerns\HasDefaultEventSubscriber;
use Authters\ServiceBus\Tracker\Concerns\HasSubscriber;
use Authters\ServiceBus\Tracker\Concerns\HasTracker;

final class MessageTracker implements BaseBusTracker
{
    use HasTracker, HasSubscriber, HasDefaultEventSubscriber, DetectMessageName;

    public const ACTION_EVENT_NAME = 'action_event';

    /**
     * @var array
     */
    protected $eventNames;

    /**
     * @var array
     */
    protected $events = [];

    public function __construct()
    {
        $this->eventNames = [self::EVENT_DISPATCH, self::EVENT_FINALIZE];

        $this->onInitialization();

        $this->onDetectMessageName();

        $this->onException();
    }

    public function createEvent(string $name, $target = null, callable $attributes = null): ActionEvent
    {
        return new DefaultActionEvent($name ?? self::ACTION_EVENT_NAME, $target, $attributes);
    }

    public function initialize(MessageActionEvent $event): void
    {
        $this->emit($event);
    }

    public function finalize(MessageActionEvent $event): void
    {
        $event->setName(self::EVENT_FINALIZE);

        $this->emit($event);
    }

    public function listenToDispatcher(callable $callback, int $priority = 1): ListenerHandler
    {
        return $this->subscribe(self::EVENT_DISPATCH, $callback, $priority);
    }

    public function listenToFinalizer(callable $callback, int $priority = 1): ListenerHandler
    {
        return $this->subscribe(self::EVENT_FINALIZE, $callback, $priority);
    }
}