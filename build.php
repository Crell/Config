<?php

declare(strict_types=1);

use Crell\Framework\Actions\StringAction;
use Crell\Framework\Router\RouteBuilder;
use Crell\Framework\Router\Router;
use Crell\Framework\TestService;
use FastRoute\DataGenerator\GroupCountBased as GroupGenerator;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupDispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

require_once 'vendor/autoload.php';

// @todo Need to figure out how to make the container and router work when both are compiled.

$routeCollector = new RouteCollector(new Std(), new GroupGenerator());

$containerBuilder = new DI\ContainerBuilder();

$routeBuilder = new RouteBuilder($routeCollector, $containerBuilder);

$routeBuilder->addRoute('GET', '/user/{id:\d+}', StringAction::class);

$dispatcher = new GroupDispatcher($routeCollector->getData());

$containerBuilder->addDefinitions([
    TestService::class => DI\autowire(),
    StringAction::class => DI\autowire(),
    Router::class => DI\autowire(),
    Psr17Factory::class => DI\autowire(),
    ResponseFactoryInterface::class => DI\autowire(Psr17Factory::class),
    StreamFactoryInterface::class => DI\autowire(Psr17Factory::class),
    RequestFactoryInterface::class => DI\autowire(Psr17Factory::class),
    ServerRequestFactoryInterface::class => DI\autowire(Psr17Factory::class),
]);

$containerBuilder->addDefinitions([
    Dispatcher::class => $dispatcher,
]);

$containerBuilder->enableCompilation(__DIR__ . '/cache');

$container = $containerBuilder->build();

$request = $container->get(RequestFactoryInterface::class)-> createRequest('GET', uri: '/user/3');


$response = $container->get(Router::class)->route($request);

$body = $response->getBody();
$body->rewind();

print $body->getContents() . PHP_EOL;

//
//switch ($routeInfo[0]) {
//    case FastRoute\Dispatcher::NOT_FOUND:
//        // ... 404 Not Found
//        print "Not found\n";
//        break;
//    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
//        $allowedMethods = $routeInfo[1];
//        // ... 405 Method Not Allowed
//        print "Not allowed\n";
//        break;
//    case FastRoute\Dispatcher::FOUND:
//        $handler = $routeInfo[1];
//        $vars = $routeInfo[2];
//        print $container->get($handler)(...$vars) . PHP_EOL;
//        break;
//}

