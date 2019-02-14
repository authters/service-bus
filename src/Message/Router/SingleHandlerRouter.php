<?php

namespace Authters\ServiceBus\Message\Router;

use Authters\ServiceBus\Exception\RuntimeException;

class SingleHandlerRouter extends MessageRouter
{
    protected function locateMessageHandler(string $messageName): iterable
    {
        if (!isset($this->map[$messageName])) {
            throw new RuntimeException("Message name $messageName not found in route map");
        }

        $messageHandler = $this->map[$messageName];

        if (is_array($messageHandler)) {
            $messageHandler = array_filter($messageHandler);

            if (\count($messageHandler) > 1) {
                throw new RuntimeException(sprintf('Single handler router can route to one handler only'));
            }

            $messageHandler = array_shift($messageHandler);
        }

        yield $this->resolveMessageHandler($messageName, $messageHandler);
    }
}