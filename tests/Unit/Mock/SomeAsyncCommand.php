<?php

namespace AuthtersTest\ServiceBus\Unit\Mock;

use Authters\ServiceBus\Contract\Message\AsyncMessage;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadTrait;

class SomeAsyncCommand extends Command implements AsyncMessage
{
    use PayloadTrait;
}