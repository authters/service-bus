<?php

return [

    'authters' => [

        /**
         * Default configuration for each bus define in this namespace
         * ----------------------------------------------------------
         *
         * Each key could be overridden in each bus config
         * and take precedence over the default provided
         * Except: array tracker subscribers and middleware which would be merged
         */
        'default' => [

            /**
             * Resolve any handler through container
             * the one provided will resolve any message handler class not bound in ioc
             */
            'service_locator' => \Authters\ServiceBus\Support\Container\IlluminateContainer::class,

            /**
             * How message should be produced async or fired immediately
             * options: "route_only_async", "route_none_async", "route_all_async"
             *
             * @see \Authters\ServiceBus\Contract\Envelope\Route\Strategy\MessageRouteStrategy
             */
            'route_strategy' => 'route_only_async',

            /**
             * Resolve any handler according to a method name strategy
             * e.g: event => onEvent, command => handle, query => find
             *
             * You should provide a valid callable otherwise if false stand
             *
             * MAKE IT PART OF MESSAGE
             */
            'callable_handler' => false,


            'message' => [

                /**
                 * Convert Message instance to array
                 */
                'converter' => \Prooph\Common\Messaging\NoOpMessageConverter::class,

                /**
                 * Simple bridge to produce async message behind an illuminate queue
                 */
                'producer' => \Authters\ServiceBus\Message\Async\IlluminateProducer::class,

                /**
                 * Allow message not to have handler
                 * Should be reserved for Domain Event only
                 */
                'allow_null_handler' => false,
            ],

            /**
             * Default event tracker and Subscribers
             */
            'tracker' => [
                'service' => \Authters\ServiceBus\Tracker\MessageTracker::class,
                'subscribers' => [
                    \Authters\ServiceBus\Message\FQCNMessageSubscriber::class,
                    \Authters\ServiceBus\Message\Validation\MessageValidatorSubscriber::class,
                ]
            ],
        ],

        /**
         * Default Middleware with their priorities
         */
        'middleware' => [
            [\Authters\ServiceBus\Envelope\Bootstrap\ContentHandlerBootstrap::class, 100],
            [\Authters\ServiceBus\Envelope\Bootstrap\LoggingBootstrap::class, 51],
            [\Authters\ServiceBus\Envelope\Bootstrap\MessageTrackerBootstrap::class, 50],
        ],

        'buses' => [

            'command' => [

                'default' => [
                    'service_bus' => \Authters\ServiceBus\CommandBus::class,
                    'middleware' => [],
                    'route' => \Authters\ServiceBus\Envelope\Route\CommandRoute::class,
                    'router' => \Authters\ServiceBus\Message\Router\Defaults\CommandRouter::class,
                    'routes' => [

                    ]
                ]
            ],


            'query' => [

                'default' => [
                    'service_bus' => \Authters\ServiceBus\QueryBus::class,
                    'middleware' => [],
                    'route' => \Authters\ServiceBus\Envelope\Route\QueryRoute::class,
                    'router' => \Authters\ServiceBus\Message\Router\Defaults\QueryRouter::class,
                    'routes' => [

                    ]
                ]
            ],

            'event' => [

                'default' => [
                    'service_bus' => \Authters\ServiceBus\EventBus::class,
                    'middleware' => [],
                    'route' => \Authters\ServiceBus\Envelope\Route\EventRoute::class,
                    'router' => \Authters\ServiceBus\Message\Router\Defaults\EventRouter::class,
                    'message' => [
                        'allow_null_handler' => true,
                    ],
                    'callable_handler' => \Authters\ServiceBus\Envelope\Route\Handler\OnEventHandler::class,
                    'routes' => [

                    ]
                ]
            ]
        ]
    ]
];