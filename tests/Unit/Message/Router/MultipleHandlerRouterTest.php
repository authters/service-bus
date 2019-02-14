<?php

namespace AuthtersTest\ServiceBus\Unit\Message\Router;

use Authters\ServiceBus\Message\Router\MultipleHandlerRouter;
use AuthtersTest\ServiceBus\Example\Mock\SomeMessageHandler;
use AuthtersTest\ServiceBus\TestCase;
use Illuminate\Container\Container;
use Psr\Container\ContainerInterface;

class MultipleHandlerRouterTest extends TestCase
{
    /**
     * @test
     */
    public function it_transform_single_handler_into_array(): void
    {
        $message = 'foo';
        $handlers = new SomeMessageHandler();

        $router = $this->getInstance([$message => $handlers]);

        $count = 0;
        foreach ($router->route($message) as $handlers) {
            foreach ($handlers as $handler) {
                $this->assertInstanceOf(SomeMessageHandler::class, $handler);
                $count++;
            }
        }

        $this->assertEquals(1, $count);
    }

    /**
     * @test
     */
    public function it_generate_handlers(): void
    {
        $message = 'foo';
        $handlers = [
            new SomeMessageHandler(),
            new SomeMessageHandler()
        ];

        $router = $this->getInstance([$message => $handlers]);

        $count = 0;
        foreach ($router->route($message) as $handlers) {
            foreach ($handlers as $handler) {
                $this->assertInstanceOf(SomeMessageHandler::class, $handler);
                $count++;
            }
        }

        $this->assertEquals(2, $count);
    }

    /**
     * @test
     */
    public function it_resolve_string_message_handler(): void
    {
        $app = new Container();
        $app->bind(SomeMessageHandler::class);

        $message = 'foo';
        $handlers = [SomeMessageHandler::class, SomeMessageHandler::class, SomeMessageHandler::class];

        $router = $this->getInstance([$message => $handlers], $app);

        $count = 0;
        foreach ($router->route($message) as $handlers) {
            foreach ($handlers as $handler) {
                $this->assertInstanceOf(SomeMessageHandler::class, $handler);
                $count++;
            }
        }

        $this->assertEquals(3, $count);

    }

    /**
     * @test
     * @expectedException \Authters\ServiceBus\Exception\RuntimeException
     */
    public function it_raise_exception_when_string_handler_can_noy_be_resolved(): void
    {
        $messageName = 'foo';
        $handlers = [SomeMessageHandler::class];

        $exceptionMessage = 'No service locator has been set for message handler ' . SomeMessageHandler::class;
        $this->expectExceptionMessage($exceptionMessage);

        $router = $this->getInstance([$messageName => $handlers]);

        foreach ($router->route($messageName) as $handlers) {
            foreach ($handlers as $handler) {
            }
        }

    }

    /**
     * @test
     * @expectedException \Authters\ServiceBus\Exception\RuntimeException
     */
    public function it_raise_exception_when_message_name_is_not_found_in_map(): void
    {
        $messageName = 'foo';

        $exceptionMessage = "Message name $messageName not found in route map";
        $this->expectExceptionMessage($exceptionMessage);

        $router = $this->getInstance([]);

        foreach ($router->route($messageName) as $handlers) {
            foreach ($handlers as $handler) {
            }
        }
    }

    /**
     * @test
     * @expectedException \Authters\ServiceBus\Exception\RuntimeException
     */
    public function it_raise_exception_when_null_handler_is_not_allowed(): void
    {
        $messageName = 'foo';

        $exceptionMessage = "Message handler is mandatory for message name $messageName";
        $this->expectExceptionMessage($exceptionMessage);

        $router = $this->getInstance([$messageName => []]);

        foreach ($router->route($messageName) as $handlers) {
            foreach ($handlers as $handler) {
            }
        }
    }

    public function getInstance(array $map, ContainerInterface $container = null, bool $allowNullHandler = false): MultipleHandlerRouter
    {
        return new MultipleHandlerRouter($map, $container, $allowNullHandler);
    }
}
