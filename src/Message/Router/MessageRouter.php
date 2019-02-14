<?php

namespace Authters\ServiceBus\Message\Router;

use Authters\ServiceBus\Contract\Message\Router\Router;
use Authters\ServiceBus\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

abstract class MessageRouter implements Router
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var bool
     */
    protected $allowNullMessageHandler;

    /**
     * @var iterable
     */
    protected $map;

    public function __construct(iterable $map = [],
                                ContainerInterface $container = null,
                                bool $allowNullMessageHandler = false)
    {
        $this->map = $map;
        $this->container = $container;
        $this->allowNullMessageHandler = $allowNullMessageHandler;
    }

    public function route(string $messageName): iterable
    {
        yield $this->locateMessageHandler($messageName);
    }

    protected function resolveMessageHandler(string $messageName, $messageHandler): object
    {
        if (!$this->isMessageHandlerTypeSupported($messageHandler)) {
            $message = "Message handler for message name $messageName ";
            $message .= "must be a string, an object or a callable";

            throw new RuntimeException($message);
        }

        if (is_string($messageHandler)) {
            if (!$this->container) {
                throw new RuntimeException(
                    "No service locator has been set for message handler $messageHandler"
                );
            }

            return $this->container->get($messageHandler);
        }

        return $messageHandler;
    }

    protected function isMessageHandlerTypeSupported($messageHandler): bool
    {
        return !(!is_object($messageHandler) && !is_callable($messageHandler) && !is_string($messageHandler));
    }

    abstract protected function locateMessageHandler(string $messageName): iterable;
}