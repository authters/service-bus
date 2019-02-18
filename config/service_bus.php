<?php

return [

    'authters' => [

        /**
         * Default configuration for each bus define in this namespace
         * ----------------------------------------------------------
         *
         * Each key could be overridden in each bus config
         * and take precedence over the default provided
         * Excepts: array tracker subscribers and array middleware which would be merged
         */
        'default' => [

            'message' => [

                /**
                 * Convert Message instance to array
                 */
                'converter' => \Prooph\Common\Messaging\NoOpMessageConverter::class,

                /**
                 * How message should be produced async or fired immediately
                 * options: "route_only_async", "route_none_async", "route_all_async"
                 *
                 * @see \Authters\ServiceBus\Contract\Envelope\Route\Strategy\MessageRouteStrategy
                 */
                'route_strategy' => 'route_only_async',

                /**
                 * Simple bridge to produce async message behind an illuminate queue
                 */
                'producer' => \Authters\ServiceBus\Message\Async\IlluminateProducer::class,

                /**
                 * Message handler
                 */
                'handler' => [

                    /**
                     * Allow message not to have handler
                     * Should be reserved for Domain Event only
                     */
                    'allow_null' => false,

                    /**
                     * Resolve any handler according to a method name strategy
                     * e.g: event => onEvent, command => handle, query => find
                     * @see \Authters\ServiceBus\Envelope\Route\Handler\OnEventHandler
                     *
                     * Or provide a valid callable otherwise if false stand
                     * @see \Authters\ServiceBus\Envelope\Route\Handler\CallableHandler
                     */
                    'to_callable' => false,

                    /**
                     * Resolve any class string handler through ioc
                     * the one provided resolve them dynamically
                     */
                    'resolver' => \Authters\ServiceBus\Support\Container\IlluminateContainer::class,
                ],
            ],

            'tracker' => [

                /**
                 * Default event tracker providing default events
                 */
                'service' => \Authters\ServiceBus\Tracker\DefaultMessageTracker::class,

                /**
                 * Interact with defined events tracker
                 */
                'subscribers' => [

                    /**
                     * Transform array message to a valid message instance
                     */
                    \Authters\ServiceBus\Message\FQCNMessageSubscriber::class,

                    /**
                     * Validate / PreValidate message with the Illuminate Validator
                     * Redundant with VO but useful for checking uniqueness/existence on PreValidation
                     */
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
                        'handler' => [
                            'allow_null' => true,
                            'to_callable' =>\Authters\ServiceBus\Envelope\Route\Handler\OnEventHandler::class,
                        ],
                    ],
                    'routes' => [

                    ]
                ]
            ]
        ]
    ]
];