<?php

namespace Authters\ServiceBus\Envelope\Route\Handler;

use Authters\ServiceBus\Exception\RuntimeException;

class CallableHandler
{
    /**
     * @var string
     */
    private $methodName;

    public function __construct(string $methodName)
    {
        $this->methodName = $methodName;
    }

    public function __invoke(object $messageHandler): callable
    {
        if (!\is_callable([$messageHandler, $this->methodName])) {
            throw new RuntimeException("Method name $this->methodName missing from message handler" . \get_class($messageHandler));
        }

        return \Closure::fromCallable([$messageHandler, $this->methodName]);
    }
}