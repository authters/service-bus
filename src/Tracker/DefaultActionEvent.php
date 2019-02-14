<?php

namespace Authters\ServiceBus\Tracker;

use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Tracker\Concerns\HasActionEvent;
use Authters\ServiceBus\Tracker\Concerns\HasAttributes;

class DefaultActionEvent implements MessageActionEvent
{
    use HasAttributes, HasActionEvent;

    public function message()
    {
        return $this->get(self::MESSAGE_ATTRIBUTE);
    }

    public function setMessage($message)
    {
        $this->set(self::MESSAGE_ATTRIBUTE, $message);
    }

    public function messageName(): ?string
    {
        return $this->get(self::MESSAGE_NAME_ATTRIBUTE);
    }

    public function setMessageName(string $messageName): void
    {
        $this->set(self::MESSAGE_NAME_ATTRIBUTE, $messageName);
    }

    public function isMessageHandled(): bool
    {
        $handled = $this->get(self::MESSAGE_HANDLED_ATTRIBUTE, false);

        return is_bool($handled) ? $handled : false;
    }

    public function setMessageHandled(bool $handled): void
    {
        $this->set(self::MESSAGE_HANDLED_ATTRIBUTE, $handled);
    }

    public function messageHandler()
    {
        return $this->get(self::MESSAGE_HANDLER_ATTRIBUTE);
    }

    public function setMessageHandler(callable $handler): void
    {
        $this->set(self::MESSAGE_HANDLER_ATTRIBUTE, $handler);
    }

    public function hasException(): bool
    {
        return $this->get(self::MESSAGE_EXCEPTION_ATTRIBUTE) instanceof \Throwable;
    }

    public function setException(\Throwable $exception = null): void
    {
        $this->set(self::MESSAGE_EXCEPTION_ATTRIBUTE, $exception);
    }

    public function exception(): ?\Throwable
    {
        return $this->get(self::MESSAGE_EXCEPTION_ATTRIBUTE);
    }
}