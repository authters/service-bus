<?php

namespace AuthtersTest\ServiceBus\Example\Mock;

class SomeMessageHandler
{
    /**
     * @var mixed
     */
    private $message;

    public function __invoke($message): void
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}