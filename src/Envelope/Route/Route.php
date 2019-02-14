<?php

namespace Authters\ServiceBus\Envelope\Route;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Contract\Message\Router\Router;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Exception\MessageCollection;
use Authters\ServiceBus\Exception\RuntimeException;

abstract class Route implements Middleware
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var callable
     */
    private $callableHandler;

    /**
     * @var bool
     */
    private $isExceptionCollectible;

    /**
     * @var array
     */
    private $collectedExceptions = [];

    public function __construct(Router $router,
                                callable $callableHandler = null,
                                bool $isExceptionCollectible = false)
    {
        $this->router = $router;
        $this->callableHandler = $callableHandler;
        $this->isExceptionCollectible = $isExceptionCollectible;
    }

    public function handle(Envelope $envelope, callable $next)
    {
        foreach ($this->router->route($envelope->messageName()) as $messageHandlers) {
            foreach ($messageHandlers as $messageHandler) {
                if ($messageHandler) {
                    $envelope = $this->resolve($envelope, $this->toCallable($messageHandler));
                }

                if (!$this->collectedExceptions) {
                    $envelope->markMessageReceived();
                }
            }
        }

        if ($this->collectedExceptions) {
            throw MessageCollection::collected($this->collectedExceptions);
        }

        return $next($envelope);
    }

    private function resolve(Envelope $envelope, callable $messageHandler): Envelope
    {
        try {
            $envelope = $this->processMessageHandler($envelope, $messageHandler);
        } catch (\Throwable $exception) {
            if (!$this->isExceptionCollectible) {
                throw $exception;
            }

            $this->collectedExceptions[] = $exception;
        }

        return $envelope;
    }

    private function toCallable(object $messageHandler): callable
    {
        switch ($messageHandler) {
            case is_callable($messageHandler):
                return $messageHandler;
            case null !== $this->callableHandler:
                return ($this->callableHandler)($messageHandler);
            default:
                throw new RuntimeException('Message handler must be a callable');
        }
    }

    abstract protected function processMessageHandler(Envelope $envelope, callable $messageHandler): Envelope;

    abstract protected function supportBus(Envelope $envelope): bool;
}