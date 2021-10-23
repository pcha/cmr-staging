<?php


namespace CMR\Staging\App\Core\Business\UseCases;


use Exception;
use Throwable;

class InvalidEntityDataException extends Exception
{
    private const EXCEPTION_CODE = 200;

    public function __construct(string $entityClass, array $subjectData, Throwable $previous = null)
    {
        $errorMessage = "The entity $entityClass couldn't be constructed with the given data " . print_r($subjectData, true);
        parent::__construct($errorMessage, self::EXCEPTION_CODE, $previous);
    }

}