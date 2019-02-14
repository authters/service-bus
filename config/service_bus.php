<?php
return [

    'authters' => [

        'default' => [
            'service_locator' => \Authters\ServiceBus\Support\Container\IlluminateContainer::class,
            'route_strategy' => \Authters\ServiceBus\Contract\Envelope\Route\Strategy\MessageRouteStrategy::ROUTE_ONLY_ASYNC,
            'callable_handler' => false,
            'message' => [
                'converter' => \Prooph\Common\Messaging\NoOpMessageConverter::class,
                'producer' => \Authters\ServiceBus\Message\Async\IlluminateProducer::class,
                'tracker' => \Authters\ServiceBus\Tracker\MessageTracker::class,
                'allow_null_handler' => false,
            ]
        ],

        'middleware' => [
            [\Authters\ServiceBus\Envelope\Bootstrap\ContentHandlerBootstrap::class, 100],
            [\Authters\ServiceBus\Envelope\Bootstrap\LoggingBootstrap::class, 51],
            [\Authters\ServiceBus\Envelope\Bootstrap\MessageTrackerBootstrap::class, 50],
            [\Authters\ServiceBus\Envelope\Route\FQCNRouteMessageFactory::class, 25]
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
                    'routes' => [

                    ]
                ]
            ]

        ]
    ]
];