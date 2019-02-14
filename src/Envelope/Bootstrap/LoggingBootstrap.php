<?php

namespace Authters\ServiceBus\Envelope\Bootstrap;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Tracker\DefaultActionEvent;
use Psr\Log\LoggerInterface;

class LoggingBootstrap implements Middleware
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Envelope $envelope, callable $next)
    {
        $message = $envelope->getMessage();
        $messageName = $envelope->messageName();

        $context = [
            'message' => $message,
            'message_name' => $messageName
        ];

        $this->logger->debug("Starting handling message $messageName", $context);

        try {
            $envelope->getMessageTracker()->subscribe('dispatch', [$this, ['onDispatching']]);
            $envelope->getMessageTracker()->subscribe('finalize', [$this, ['onFinalizing']]);

            $envelope = $next($envelope);

        } catch (\Throwable $exception) {
            $context['exception'] = $exception;
            $this->logger->warning("An exception occurred while handling message $messageName", $context);

            throw $exception;
        }

        $this->logger->debug("Finished handling message $messageName", $context);

        return $envelope;
    }

    public function onDispatching(DefaultActionEvent $event): void
    {
        $messageName = $event->messageName();

        $this->logger->debug("Starting dispatching message $messageName", $event->all());
    }

    public function onFinalizing(DefaultActionEvent $event): void
    {
        $messageName = $event->messageName();

        $this->logger->debug("Finalizing dispatched message $messageName", $event->all());
    }
}