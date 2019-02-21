<?php

namespace AuthtersTest\ServiceBus\Unit\Support\Events\Subscribers;

use Authters\ServiceBus\Contract\Message\Validation\PreValidateMessage;
use Authters\ServiceBus\Contract\Message\Validation\ValidateMessage;
use Authters\ServiceBus\Exception\ValidationException;
use Authters\ServiceBus\Support\Events\Subscriber\MessageValidatorSubscriber;
use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\DefaultActionEvent;
use Authters\Tracker\Event\AbstractNamedEvent;
use AuthtersTest\ServiceBus\TestCase;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadTrait;

class MessageValidatorSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function it_validate_message(): void
    {
        $this->validator->expects($this->once())->method('fails')->willReturn(false);

        $ev = new MessageValidatorSubscriber($this->factory);
        $ev->applyTo()($actionEvent = $this->newActionEvent(false));
    }

    /**
     * @test
     */
    public function it_set_exception_on_action_event_when_message_validation_fail(): void
    {
        $this->validator->expects($this->once())->method('fails')->willReturn(true);

        $ev = new MessageValidatorSubscriber($this->factory);
        $ev->applyTo()($actionEvent = $this->newActionEvent(false));

        $this->assertInstanceOf(ValidationException::class, $actionEvent->exception());
    }

    /**
     * @test
     * @expectedException \Authters\ServiceBus\Exception\ValidationException
     */
    public function it_raise_exception_on_validation_failure(): void
    {
        $this->validator->expects($this->once())->method('fails')->willReturn(true);

        $ev = new MessageValidatorSubscriber($this->factory);
        $ev->applyTo()($actionEvent = $this->newActionEvent(true));
    }


    private function newActionEvent(bool $preValidateMessage): ActionEvent
    {
        $message = $preValidateMessage
            ? $this->preValidateMessage()
            : $this->validateMessage();

        return new DefaultActionEvent($this->someEvent(), function (ActionEvent $event) use ($message) {
            $event->setMessage($message);
        });
    }

    private function someEvent(): NamedEvent
    {
        return new class extends AbstractNamedEvent
        {
            public function priority(): int
            {
                return 0;
            }

            public function name(): string
            {
                return 'foo';
            }
        };
    }

    private function validateMessage(): ValidateMessage
    {
        return new class extends Command implements ValidateMessage
        {
            use PayloadTrait;

            public function getValidationRules(): array
            {
                return ['foo' => 'bar'];
            }
        };
    }

    private function preValidateMessage(): PreValidateMessage
    {
        return new class extends Command implements PreValidateMessage
        {
            use PayloadTrait;

            public function getValidationRules(): array
            {
                return ['foo' => 'bar'];
            }
        };
    }

    private $factory;
    private $validator;

    protected function setUp()
    {
        $this->factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = $this->getMockForAbstractClass(Validator::class);

        $this->factory->expects($this->once())->method('make')->willReturn($this->validator);
    }
}