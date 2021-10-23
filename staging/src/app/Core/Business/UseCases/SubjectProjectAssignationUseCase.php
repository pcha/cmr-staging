<?php

namespace CMR\Staging\App\Core\Business\UseCases;

use CMR\Staging\App\Core\Business\Repositories\RepositoryInternalException;
use CMR\Staging\App\Core\Business\Repositories\SubjectsRepositoryInterface as SubjectsRepository;

class SubjectProjectAssignationUseCase
{
    public function __construct(
        private SubjectsRepository $subjectsRepository
    )
    {
    }

    /**
     * @param int $subjectId
     * @param int $projectId
     * @param int $repositoryId
     * @throws ProjectAlreadyAssignedException
     * @throws SubjectNotFoundException
     * @throws RepositoryInternalException
     */
    public function execute(int $subjectId, int $projectId, int $repositoryId): void
    {
        if (!$this->subjectsRepository->find($subjectId, $repositoryId)) {
            throw new SubjectNotFoundException($subjectId);
        }
        $assignedProjects = $this->subjectsRepository->listAssignedProjects($subjectId, $repositoryId);
        foreach ($assignedProjects as $project) {
            if ($projectId == $project->getId()) {
                throw new ProjectAlreadyAssignedException($projectId, $subjectId);
            }
        }
        $this->subjectsRepository->assignProject($subjectId, $projectId, $repositoryId);
    }
}