<?php

namespace Authters\ServiceBus\Contract\Manager;

use Authters\ServiceBus\Contract\Messager;

interface ServiceBusManager
{
    public function command(string $busName = null): Messager;

    public function event(string $busName = null): Messager;

    public function query(string $busName = null): Messager;
}