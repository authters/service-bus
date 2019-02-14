<?php

namespace Authters\ServiceBus\Support\Container;

use Illuminate\Contracts\Container\Container;
use Psr\Container\ContainerInterface;

final class IlluminateContainer implements ContainerInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var bool
     */
    private $resolveServiceDynamically;

    public function __construct(Container $container, bool $resolveServiceDynamically = true)
    {
        $this->container = $container;
        $this->resolveServiceDynamically = $resolveServiceDynamically;
    }

    public function get($id)
    {
        if ($this->has($id)) {
            return $this->container->make($id);
        }

        throw new ServiceNotFound("Service not found with id $id");
    }

    public function has($id): bool
    {
        if ($this->container->bound($id)) {
            return true;
        }

        if (class_exists($id) && $this->resolveServiceDynamically) {
            return true;
        }

        return false;
    }
}