<?php

namespace CMR\Staging\App\Core\Business\UseCases;

use Exception;
use Throwable;

class ProjectAlreadyAssignedException extends Exception
{
    const EXCEPTION_CODE = 202;

    public function __construct(int $projectId, int $subjectId, Throwable $previous = null)
    {
        $message = "The project {$projectId} is already assigned to the Subject {$subjectId}";
        parent::__construct($message, self::EXCEPTION_CODE, $previous);
    }

}