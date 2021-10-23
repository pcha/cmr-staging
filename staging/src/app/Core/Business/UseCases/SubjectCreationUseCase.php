<?php

namespace CMR\Staging\App\Core\Business\UseCases;


use CMR\Staging\App\Core\Business\Repositories\InvalidEntityException;
use CMR\Staging\App\Core\Business\Repositories\RepositoryInternalException;
use CMR\Staging\App\Core\Business\Repositories\SubjectsRepositoryInterface as SubjectsRepository;
use CMR\Staging\App\Core\Entities\Subject;
use TypeError;

class SubjectCreationUseCase
{
    public function __construct(
        private SubjectsRepository $subjectsRepository
    )
    {
    }

    /**
     * @throws InvalidEntityException
     * @throws AlreadyExistentSubjectException
     * @throws InvalidEntityDataException
     * @throws RepositoryInternalException
     */
    public function execute(array $subjectData, int $repositoryId): Subject
    {
        if ($subject = $this->subjectsRepository->find($subjectData['id'], $repositoryId)) {
            throw new AlreadyExistentSubjectException($subject);
        }
        $subject = $this->getSubject($subjectData);
        $this->subjectsRepository->create($subject, $repositoryId);
        return $subject;
    }

    /**
     * @param array $subjectData
     * @return Subject
     * @throws InvalidEntityDataException
     */
    protected function getSubject(array $subjectData): Subject
    {
        try {
            $subject = new Subject(
                $subjectData['id'] ?? null,
                $subjectData['firstName'] ?? null,
                $subjectData['lastName'] ?? null,
                $subjectData['title'] ?? null,
                $subjectData['licenseNumber'] ?? null,
            );
        } catch (TypeError $e) {
            throw new InvalidEntityDataException(Subject::class, $subjectData, $e);
        }
        return $subject;
    }


}