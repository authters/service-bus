<?php

namespace Authters\ServiceBus\Exception;

class MessageCollection extends MessageDispatchedFailure
{
    /**
     * @var \Throwable[]
     */
    private $exceptionCollection;

    public static function collected(\Throwable ...$exceptions): self
    {
        $messages = '';

        foreach ($exceptions as $exception) {
            $messages .= $exception->getMessage() . "\n";
        }

        $self = new self("At least one event listener caused an exception. Check listener exceptions for details:\n$messages");
        $self->exceptionCollection = $exceptions;

        return $self;
    }

    /**
     * @return \Throwable[]
     */
    public function listenerExceptions(): array
    {
        return $this->exceptionCollection;
    }
}