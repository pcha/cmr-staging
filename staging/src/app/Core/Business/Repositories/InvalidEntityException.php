<?php


namespace CMR\Staging\App\Core\Business\Repositories;


use Exception;
use Throwable;

class InvalidEntityException extends Exception
{
    const EXCEPTION_CODE = 211;

    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, self::EXCEPTION_CODE, $previous);
    }
}