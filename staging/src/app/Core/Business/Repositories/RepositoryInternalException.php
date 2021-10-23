<?php


namespace CMR\Staging\App\Core\Business\Repositories;


use Exception;
use Throwable;

class RepositoryInternalException extends Exception
{
    private const EXCEPTION_CODE = 210;

    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, self::EXCEPTION_CODE, $previous);
    }
}