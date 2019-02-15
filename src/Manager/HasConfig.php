<?php

namespace Authters\ServiceBus\Manager;

use Authters\ServiceBus\CommandBus;
use Authters\ServiceBus\EventBus;
use Authters\ServiceBus\Exception\RuntimeException;
use Authters\ServiceBus\QueryBus;

trait HasConfig
{
    protected function valueFrom(string $serviceKey, array $busConfig = null, $defaultValue = null)
    {
        if (null === $busConfig) {
            return array_get($this->getGlobalConfiguration(), $serviceKey);
        }

        if (null !== $value = array_get($busConfig, $serviceKey)) {
            return $value;
        }

        return $defaultValue;
    }

    protected function getGlobalConfiguration(): array
    {
        $key = sprintf('service_bus.%s', $this->namespace);

        return $this->app->make('config')->get($key);
    }

    protected function getBusConfiguration(string $type, string $name): array
    {
        $defaultType = $this->determineBusType($type);

        $id = sprintf('service_bus.%s.buses.%s.%s', $this->namespace, $defaultType, $name);

        $config = $this->app->make('config')->get($id);

        if (!$config) {
            $message = "Configuration not found for ";
            $message .= "bus type $type and bus name $name and namespace $this->namespace";

            throw new RuntimeException($message);
        }

        return $config;
    }

    protected function determineBusType(string $busClass): string
    {
        switch ($busClass) {
            case CommandBus::class:
                return 'command';
            case EventBus::class:
                return 'event';
            case QueryBus::class:
                return 'query';
        }

        throw new RuntimeException("Invalid bus type $busClass");
    }

    protected function determineBusKey(string $busType, string $busName): string
    {
        $type = $this->determineBusType($busType);

        return mb_strtolower(sprintf('%s:%s.%s', $this->namespace, $type, $busName));
    }
}