<?php

namespace Authters\ServiceBus\Contract\Message;

use Prooph\Common\Messaging\Message;

interface MessageProducer
{
    public function __invoke(Message $message): void;
}