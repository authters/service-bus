<?php

namespace Authters\ServiceBus\Contract\Message\Validation;

use Prooph\Common\Messaging\Message;

interface ValidateMessage extends Message
{
    public function getValidationRules(): array;
}