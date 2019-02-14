<?php

namespace Authters\ServiceBus\Tracker;

use Authters\ServiceBus\Contract\Tracker\MessageTracker as BaseBusTracker;
use Authters\ServiceBus\Support\DetectMessageName;
use Authters\ServiceBus\Tracker\Concerns\HasEventTracker;
use Authters\ServiceBus\Tracker\Concerns\HasMessageTracker;
use Authters\ServiceBus\Tracker\Concerns\HasSubscriber;
use Authters\ServiceBus\Tracker\Concerns\HasTracker;

final class MessageTracker implements BaseBusTracker
{
    public const ACTION_EVENT_NAME = 'action_event';

    use HasTracker, HasSubscriber, HasMessageTracker, HasEventTracker, DetectMessageName;

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
        $this->eventNames = $this->getEventNames();

        $this->attachToTracker();
    }
}