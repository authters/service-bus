<?php

namespace Authters\ServiceBus\Contract\Message\Router;

interface Router
{
    public function route(string $messageName): iterable;
}