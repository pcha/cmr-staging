<?php

namespace CMR\Staging\App\Core\Business\Repositories;

use CMR\Staging\App\Core\Entities\Project;
use CMR\Staging\App\Core\Entities\Subject;

interface SubjectsRepositoryInterface
{
    /**
     * @param $id
     * @param int $repositoryId
     * @return Subject|null
     * @throws RepositoryInternalException
     */
    public function find($id, int $repositoryId): ?Subject;

    /**
     * @param Subject $subject
     * @param int $repositoryId
     * @throws RepositoryInternalException
     * @throws InvalidEntityException
     */
    public function create(Subject $subject, int $repositoryId): void;

    /**
     * @param int $subjectId
     * @param int $repositoryId
     * @return Project[]
     * @throws RepositoryInternalException
     */
    public function listAssignedProjects(int $subjectId, int $repositoryId): array;

    /**
     * @param int $subjectId
     * @param int $projectId
     * @param int $repositoryId
     * @throws RepositoryInternalException
     */
    public function assignProject(int $subjectId, int $projectId, int $repositoryId): void;
}