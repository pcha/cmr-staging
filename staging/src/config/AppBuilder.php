<?php


namespace CMR\Staging\Config;


use DI\ContainerBuilder;
use Slim\App;
use Slim\Factory\AppFactory;

class AppBuilder
{
    public static function build(): App
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->addDefinitions(Dependencies::getDefinitions());

        $container = $containerBuilder->build();

        AppFactory::setContainer($container);
        $app = AppFactory::create();

        Router::setRoutes($app);

        $middlewares = MiddlewaresRegistry::provide();
        foreach ($middlewares as $md) {
            if (is_string($md)) {
                $md = $container->get($md);
                $app->add($md);
            }
        }
        return $app;
    }
}