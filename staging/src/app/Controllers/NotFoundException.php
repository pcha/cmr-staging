<?php


namespace CMR\Staging\App\Controllers;


class NotFoundException extends AbstractHttpException
{

    protected function getHttpCode(): int
    {
        return 404;
    }
}