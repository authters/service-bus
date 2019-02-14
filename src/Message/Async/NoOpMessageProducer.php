<?php

namespace Authters\ServiceBus\Message\Async;

use Authters\ServiceBus\Contract\Message\MessageProducer;
use Prooph\Common\Messaging\Message;

class NoOpMessageProducer implements MessageProducer
{
    public function __invoke(Message $message): void
    {
    }
}