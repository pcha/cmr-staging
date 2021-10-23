<?php

namespace CMR\Staging\App\Core\Business\UseCases;

use CMR\Staging\App\Core\Business\Repositories\SubjectsRepositoryInterface as SubjectsRepository;
use CMR\Staging\App\Core\Entities\Project;
use CMR\Staging\App\Core\Entities\Subject;
use PHPUnit\Framework\TestCase;
use Throwable;

class SubjectProjectAssignationUseCaseTest extends TestCase
{
    /**
     * @param int $projectId
     * @param array $assignedProjects
     * @param Throwable|null $expectedException
     * @dataProvider provideForTestExecute
     */
    public function testExecute(int $projectId, bool $subjectFound, array $assignedProjects, ?string $expectedException = null)
    {
        $subjectId = 1;
        $repositoryId = 2;
        $subjectRepository = $this->createMock(SubjectsRepository::class);
        $findSubject = $subjectRepository->expects($this->once())
            ->method('find')
            ->with($subjectId, $repositoryId);
        if ($subjectFound) {
            $findSubject->willReturn(new Subject(1, 'John', 'Doe', 'Dr', '123456'));
            $subjectRepository->method('listAssignedProjects')
                ->willReturn(array_map(fn($projId) => new Project($projId), $assignedProjects));
        } else {
            $findSubject->willReturn(null);
        }

        if ($expectedException) {
            $this->expectException($expectedException);
        } else {
            $subjectRepository->expects($this->once())
                ->method('assignProject')
                ->with($subjectId, $projectId, $repositoryId);
        }

        $projectAssignation = new SubjectProjectAssignationUseCase($subjectRepository);
        $projectAssignation->execute($subjectId, $projectId, $repositoryId);
    }

    /**
     * @return array[] arguments
     *
     */
    public function provideForTestExecute(): array
    {
        return [
            [4, true, [1, 2, 3], null],
            [3, true, [1, 2, 3], ProjectAlreadyAssignedException::class],
            [3, false, [], SubjectNotFoundException::class],
        ];
    }
}
