<?php

namespace Authters\ServiceBus\Message\Validation;

use Authters\ServiceBus\Contract\Message\Validation\PreValidateMessage;
use Authters\ServiceBus\Contract\Message\Validation\ValidateMessage;
use Authters\ServiceBus\Exception\ValidationException;
use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;
use Authters\Tracker\Event\Named\OnDispatched;
use Illuminate\Validation\Factory;

class MessageValidatorSubscriber extends AbstractSubscriber
{
    /**
     * @var Factory
     */
    private $validationFactory;

    public function __construct(Factory $validationFactory)
    {
        $this->validationFactory = $validationFactory;
    }

    public function applyTo(): callable
    {
        return function (ActionEvent $event) {
            $message = $event->message();

            if ($message instanceof ValidateMessage) {
                try {

                    // checkMe add metadata _message_validated = true
                    // hasBeenValidated/IsValid
                    // and reset message on event
                    $this->validate($message);
                } catch (\Throwable $exception) {
                    if ($message instanceof PreValidateMessage) {
                        throw $exception;
                    }

                    $event->setException($exception);
                }
            }
        };
    }

    protected function validate(ValidateMessage $message): void
    {
        $validator = $this->validationFactory->make($message->payload(), $message->getValidationRules());

        if ($validator->fails()) {
            throw ValidationException::withValidator($validator);
        }
    }

    public function priority(): int
    {
        return 30000;
    }

    public function subscribeTo(): NamedEvent
    {
        return new OnDispatched();
    }
}