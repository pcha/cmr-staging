<?php


namespace CMR\Staging\App\Controllers;


class BadRequestException extends AbstractHttpException
{
    protected function getHttpCode(): int
    {
        return 400;
    }
}