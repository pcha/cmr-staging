<?php


namespace CMR\Staging\App\Controllers;


use Throwable;

class BadGatewayException extends AbstractHttpException
{

    protected function getHttpCode(): int
    {
        return 502;
    }

    public function __construct(Throwable $previous = null)
    {
        parent::__construct("The proxy server received an invalid response from an upstream server (Code: {$previous->getCode()})", $previous);
    }
}