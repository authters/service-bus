<?php

namespace Authters\ServiceBus\Contract\Tracker;

interface MessageActionEvent extends ActionEvent
{
    public function message();

    public function setMessage($message);

    public function messageName(): ?string;

    public function setMessageName(string $messageName);

    public function isMessageHandled(): bool;

    public function setMessageHandled(bool $handled): void;

    public function messageHandler();

    public function setMessageHandler(callable $handler): void;

    public function hasException(): bool;

    public function setException(\Throwable $exception = null): void;

    public function exception(): ?\Throwable;
}