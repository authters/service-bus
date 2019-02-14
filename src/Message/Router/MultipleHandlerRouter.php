<?php

namespace Authters\ServiceBus\Message\Router;

use Authters\ServiceBus\Exception\RuntimeException;

class MultipleHandlerRouter extends MessageRouter
{
    protected function locateMessageHandler(string $messageName): iterable
    {
        if (!isset($this->map[$messageName])) {
            throw new RuntimeException("Message name $messageName not found in route map");
        }

        $messageHandlers = $this->map[$messageName];

        if (!\is_array($messageHandlers)) {
            $messageHandlers = [$messageHandlers];
        }

        $messageHandlers = array_filter($messageHandlers);

        if (!$messageHandlers && $this->allowNullMessageHandler) {
            return [];
        }

        if (!$messageHandlers) {
            throw new RuntimeException("Message handler is mandatory for message name $messageName");
        }

        foreach ($messageHandlers as $messageHandler) {
            yield $this->resolveMessageHandler($messageName, $messageHandler);
        }
    }
}