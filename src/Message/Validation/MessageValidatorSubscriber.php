<?php

namespace Authters\ServiceBus\Message\Validation;

use Authters\ServiceBus\Contract\Message\Validation\PreValidateMessage;
use Authters\ServiceBus\Contract\Message\Validation\ValidateMessage;
use Authters\ServiceBus\Contract\Tracker\EventSubscriber;
use Authters\ServiceBus\Contract\Tracker\MessageActionEvent;
use Authters\ServiceBus\Contract\Tracker\Tracker;
use Authters\ServiceBus\Exception\ValidationException;
use Authters\ServiceBus\Tracker\Concerns\HasEventSubscriber;
use Illuminate\Validation\Factory;

class MessageValidatorSubscriber implements EventSubscriber
{
    use HasEventSubscriber;

    /**
     * @var Factory
     */
    private $validationFactory;

    public function __construct(Factory $validationFactory)
    {
        $this->validationFactory = $validationFactory;
    }

    public function attachToBus(Tracker $tracker, string $messageBus = null): void
    {
        $this->listenerHandlers[] = $tracker->subscribe(Tracker::EVENT_DISPATCH, function (MessageActionEvent $event) use ($messageBus) {
            $message = $event->message();

            if ($message instanceof ValidateMessage) {
                try {
                    $this->validate($message);
                } catch (\Throwable $exception) {
                    if ($message instanceof PreValidateMessage) {
                        throw $exception;
                    }

                    $event->setException($exception);
                }
            }
        });
    }

    protected function validate(ValidateMessage $message): void
    {
        $validator = $this->validationFactory->make($message->payload(), $message->getValidationRules());

        if ($validator->fails()) {
            throw ValidationException::withValidator($validator);
        }
    }
}