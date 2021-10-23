<?php

namespace CMR\Staging\App\Controllers;

use Exception;
use Throwable;

abstract class AbstractHttpException extends Exception
{
    abstract protected function getHttpCode(): int;

    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, $this->getHttpCode(), $previous);
    }
}