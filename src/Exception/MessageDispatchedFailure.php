<?php

namespace Authters\ServiceBus\Exception;

class MessageDispatchedFailure extends RuntimeException
{
    public static function reason(\Throwable $dispatchException): MessageDispatchedFailure
    {
        return new static('Message dispatch failed. See previous exception for details.', 422, $dispatchException);
    }
}