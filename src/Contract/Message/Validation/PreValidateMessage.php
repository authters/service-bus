<?php

namespace Authters\ServiceBus\Contract\Message\Validation;

interface PreValidateMessage extends ValidateMessage
{
    // Raise validation exception immediately
    // instead of keep on dispatching message
}