<?php


namespace CMR\Staging\App\Controllers;


class ForbidenException extends AbstractHttpException
{

    protected function getHttpCode(): int
    {
        return 403;
    }
}