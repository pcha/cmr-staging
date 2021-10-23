<?php


namespace CMR\Staging\Config;


use CMR\Staging\Infrastructure\Middlewares\CorsMiddleware;
use CMR\Staging\Infrastructure\Middlewares\ExceptionHandlingMiddleware;

class MiddlewaresRegistry
{
    public static function provide(): array
    {
        return [
            ExceptionHandlingMiddleware::class,
            CorsMiddleware::class,
        ];
    }
}