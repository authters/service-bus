<?php

namespace Authters\ServiceBus\Contract\Message\Guard;

interface AuthorizationService
{
    public function isGranted(string $messageName, $context = null): bool;
}