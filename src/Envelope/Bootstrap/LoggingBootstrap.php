<?php

namespace Authters\ServiceBus\Envelope\Bootstrap;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\ServiceBus\Support\DetectMessageName;
use Psr\Log\LoggerInterface;

class LoggingBootstrap implements Middleware
{
    use DetectMessageName;

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
        $messageName = $this->detectMessageName($message);

        $context = [
            'message' => $message,
            'message_name' => $messageName
        ];

        $this->logger->debug("Starting handling message $messageName", $context);

        try {
            $envelope = $next($envelope);
        } catch (\Throwable $exception) {
            $context['exception'] = $exception;
            $this->logger->warning("An exception occurred while handling message $messageName", $context);

            throw $exception;
        }

        $this->logger->debug("Finished handling message $messageName", $context);

        return $envelope;
    }
}