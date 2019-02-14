<?php

namespace Authters\ServiceBus\Contract\Envelope;

use Authters\ServiceBus\Envelope\Envelope;

interface Middleware
{
    public function handle(Envelope $envelope, callable $next);
}