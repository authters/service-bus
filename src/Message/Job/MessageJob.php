<?php

namespace Authters\ServiceBus\Message\Job;

use Illuminate\Contracts\Container\Container;

final class MessageJob
{
    /**
     * @var array
     */
    private $payload;

    /**
     * @var string
     */
    private $busType;

    public function __construct(array $payload, string $busType)
    {
        $this->payload = $payload;
        $this->busType = $busType;
    }

    public function handle(Container $container): void
    {
       $container->make($this->busType)->dispatch($this->payload);
    }
}