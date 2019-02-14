<?php

namespace Authters\ServiceBus\Envelope\Guard;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Contract\Message\Guard\AuthorizationService;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Exception\Unauthorized;

class AuthorizeRoute implements Middleware
{
    /**
     * @var AuthorizationService
     */
    private $authorization;

    public function __construct(AuthorizationService $authorization)
    {
        $this->authorization = $authorization;
    }

    public function handle(Envelope $envelope, callable $next)
    {
        if (!$this->authorization->isGranted($envelope->messageName())) {
            throw new Unauthorized('You are not authorized to access the resource');
        }

        return $next($envelope);
    }
}