<?php

namespace Authters\ServiceBus\Tracker\Concerns;

use Authters\ServiceBus\Exception\RuntimeException;

trait HasActionEvent
{
    /**
     * @var bool
     */
    protected $stopPropagation = false;

    public function __construct(string $name, $target = null, callable $callback = null)
    {
        $this->setName($name);
        $this->setTarget($target);

        if ($callback) {
            $callback($this);
        }
    }

    public function getName()
    {
        if (null !== $name = $this->get(self::ACTION_EVENT_ATTRIBUTE)) {
            return $name;
        }

        throw new RuntimeException('No name set for action event');
    }

    public function getTarget()
    {
        return $this->get(self::ACTION_EVENT_TARGET_ATTRIBUTE);
    }

    public function setName(string $name): void
    {
        $this->set(self::ACTION_EVENT_ATTRIBUTE, $name);
    }

    public function setTarget($target): void
    {
        $this->set(self::ACTION_EVENT_TARGET_ATTRIBUTE, $target);
    }

    public function isPropagationStopped(): bool
    {
        return $this->stopPropagation;
    }

    public function stopPropagation(bool $stopPropagation): void
    {
        $this->stopPropagation = $stopPropagation;
    }
}