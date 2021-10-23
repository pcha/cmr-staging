<?php

namespace CMR\Staging\Config;

use CMR\Staging\App\Controllers\SubjectsController;
use CMR\Staging\Infrastructure\Middlewares\SecurityMiddleware;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy as Group;

class Router
{
    public static function setRoutes(App $app)
    {
        $app->options('/{routes:.+}', fn($request, $response, $args) => $response);
        $app->group('', function (Group $group) {
            $group->put('/subjects/{id:[0-9]+}', [SubjectsController::class, 'create']);
            $group->post('/subjects/{id:[0-9]+}/assign', [SubjectsController::class, 'assignProject']);
            /**
             * Catch-all route to serve a 404 Not Found page if none of the routes match
             * NOTE: make sure this route is defined last
             */
            $group->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', fn($request, $response) => throw new HttpNotFoundException($request));
        })->add(SecurityMiddleware::class);
    }
}