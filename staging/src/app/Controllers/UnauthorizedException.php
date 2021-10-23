<?php


namespace CMR\Staging\App\Controllers;


class UnauthorizedException extends AbstractHttpException
{
    protected function getHttpCode(): int
    {
        return 401;
    }
}