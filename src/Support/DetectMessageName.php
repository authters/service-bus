<?php

namespace Authters\ServiceBus\Support;

use Prooph\Common\Messaging\HasMessageName;

trait DetectMessageName
{
    protected function detectMessageName($message): string
    {
        if ($message instanceof HasMessageName) {
            return $message->messageName();
        }

        if (is_object($message)) {
            return \get_class($message);
        }

        if (is_string($message)) {
            return $message;
        }

        return gettype($message);
    }
}