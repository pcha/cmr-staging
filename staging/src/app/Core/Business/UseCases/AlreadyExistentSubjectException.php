<?php


namespace CMR\Staging\App\Core\Business\UseCases;


use CMR\Staging\App\Core\Entities\Subject;
use Exception;
use Throwable;

class AlreadyExistentSubjectException extends Exception
{
    const EXCEPTION_CODE = 201;

    public function __construct(Subject $subject, Throwable $previous = null)
    {
        $message = "There is already a subject with ID {$subject->getId()}";
        parent::__construct($message, self::EXCEPTION_CODE, $previous);
    }
}