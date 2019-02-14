<?php

namespace Authters\ServiceBus\Support\Container;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFound extends \RuntimeException implements NotFoundExceptionInterface
{
}