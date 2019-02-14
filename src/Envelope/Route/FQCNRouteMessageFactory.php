<?php

namespace Authters\ServiceBus\Envelope\Route;

use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Envelope\Envelope;
use Prooph\Common\Messaging\FQCNMessageFactory;

class FQCNRouteMessageFactory implements Middleware
{
    /**
     * @var FQCNRouteMessageFactory
     */
    private $messageFactory;

    public function __construct(FQCNMessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    public function handle(Envelope $envelope, callable $next)
    {
        $message = $envelope->getMessage();

        if (\is_array($message) && array_key_exists('message_name', $message)) {
            $messageName = $message['message_name'];
            unset($message['message_name']);

            $fromFactory = $this->messageFactory->createMessageFromArray(
                $messageName,
                $message
            );

            $envelope->currentActionEvent()->setMessage($fromFactory);
            $envelope->currentActionEvent()->setMessageName($messageName);

            $envelope = $envelope->wrap($fromFactory);
        }

        return $next($envelope);
    }
}