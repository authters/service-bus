<?php

namespace Authters\ServiceBus\Contract\Tracker;

interface ActionEvent
{
    public const ACTION_EVENT_ATTRIBUTE = 'actionEvent';
    public const ACTION_EVENT_TARGET_ATTRIBUTE = 'actionEventTarget';
    public const MESSAGE_ATTRIBUTE = 'message';
    public const MESSAGE_NAME_ATTRIBUTE = 'messageName';
    public const MESSAGE_HANDLER_ATTRIBUTE = 'messageHandler';
    public const MESSAGE_HANDLED_ATTRIBUTE = 'messageHandled';
    public const MESSAGE_EXCEPTION_ATTRIBUTE = 'messageException';

    public function setName(string $name): void;

    public function getName();

    public function setTarget($target): void;

    public function getTarget();

    public function isPropagationStopped(): bool;

    public function stopPropagation(bool $stopPropagation): void;

    public function set(string $key, $value);

    public function get(string $key, $default = null);

    public function has(string $key): bool;
}