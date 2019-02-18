<?php

namespace Authters\ServiceBus;

use Authters\ServiceBus\Contract\Messager as BaseBus;
use Authters\ServiceBus\Contract\Tracker\Tracker;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Tracker\DefaultMessageTracker;

abstract class Messager implements BaseBus
{
    /**
     * @var iterable
     */
    protected $map;

    /**
     * @var Tracker
     */
    private $tracker;

    public function __construct(iterable $middleware = [], Tracker $tracker = null)
    {
        $this->map = $middleware;
        $this->tracker = $tracker ?? new DefaultMessageTracker();
    }

    protected function dispatchForBus(string $busType, $message)
    {
        $envelope = new Envelope($message, $this->tracker);
        $envelope->setBusType($busType);

        return $this->dispatchMessage($envelope);
    }

    private function dispatchMessage(Envelope $envelope)
    {
        return \call_user_func($this->callableForNextMiddleware(0, $envelope), $envelope);
    }

    private function callableForNextMiddleware(int $index, Envelope $currentEnvelope): callable
    {
        if (null === $this->map) {
            $this->map = \is_array($this->map)
                ? $this->map
                : iterator_to_array($this->map);
        }

        if (!isset($this->map[$index])) {
            return function (Envelope $envelope) {
                return $envelope;
            };
        }

        $middleware = $this->map[$index];

        return function (Envelope $envelope) use ($middleware, $index, $currentEnvelope) {
            return $middleware->handle(
                $envelope,
                $this->callableForNextMiddleware($index + 1, $currentEnvelope)
            );
        };
    }
}