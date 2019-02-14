<?php

namespace AuthtersTest\ServiceBus\Unit\Mock;

use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadTrait;

class SomeCommand extends Command
{
    use PayloadTrait;
}