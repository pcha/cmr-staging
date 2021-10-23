<?php

namespace CMR\Staging\App\Security;


use Exception;
use Throwable;

class InvalidTokenException extends Exception
{
    const EXCEPTION_CODE = 310;

    public function __construct($message = "Invalid Token", Throwable $previous = null)
    {
        parent::__construct($message, self::EXCEPTION_CODE, $previous);
    }
}