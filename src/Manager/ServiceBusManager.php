<?php

namespace Authters\ServiceBus\Manager;

use Authters\ServiceBus\CommandBus;
use Authters\ServiceBus\Contract\Manager\ServiceBusManager as Manager;
use Authters\ServiceBus\Contract\Messager;
use Authters\ServiceBus\EventBus;
use Authters\ServiceBus\QueryBus;

class ServiceBusManager extends DefaultBusManager implements Manager
{
    public function command(string $busName = null): Messager
    {
        return $this->make($busName ?? 'default', CommandBus::class);
    }

    public function event(string $busName = null): Messager
    {
        return $this->make($busName ?? 'default', EventBus::class);
    }

    public function query(string $busName = null): Messager
    {
        return $this->make($busName ?? 'default', QueryBus::class);
    }

    protected function make(string $busName, string $busType): Messager
    {
        $busKey = $this->determineBusKey($busType, $busName);

        if (isset($this->buses[$busKey])) {
            return $this->buses[$busKey];
        }

        return $this->buses[$busKey] = $this->create($busName, $busType);
    }

    protected function create(string $busName, string $busType): Messager
    {
        $busConfig = $this->getBusConfiguration($busType, $busName);

        $serviceBusId = $busConfig['service_bus'];
        if ($preconfiguredServiceBus = $this->preconfiguredServiceBus($serviceBusId)) {
            return $preconfiguredServiceBus;
        }

        return new $serviceBusId(
            $this->buildMiddleware($busConfig),
            $this->newMessageTracker($busConfig)
        );
    }

    protected function preconfiguredServiceBus(string $serviceBusId): ?Messager
    {
        if (!class_exists($serviceBusId) && $this->app->bound($serviceBusId)) {
            return $this->app->make($serviceBusId);
        }

        return null;
    }
}