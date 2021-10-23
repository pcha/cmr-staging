<?php


namespace CMR\Staging\App\Core\Business\UseCases;


use Exception;
use Throwable;

class SubjectNotFoundException extends Exception
{
    const EXCEPTION_CODE = 203;

    public function __construct(int $subjectId, Throwable $previous = null)
    {
        parent::__construct("Subject $subjectId not found", self::EXCEPTION_CODE, $previous);
    }
}