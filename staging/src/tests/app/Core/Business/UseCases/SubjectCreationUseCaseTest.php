<?php

namespace CMR\Staging\Test\App\Core\Business\UseCases;

use CMR\Staging\App\Core\Business\Repositories\InvalidEntityException;
use CMR\Staging\App\Core\Business\Repositories\RepositoryInternalException;
use CMR\Staging\App\Core\Business\Repositories\SubjectsRepositoryInterface;
use CMR\Staging\App\Core\Business\UseCases\AlreadyExistentSubjectException;
use CMR\Staging\App\Core\Business\UseCases\InvalidEntityDataException;
use CMR\Staging\App\Core\Business\UseCases\SubjectCreationUseCase;
use CMR\Staging\App\Core\Entities\Subject;
use PHPUnit\Framework\TestCase;
use Throwable;

class SubjectCreationUseCaseTest extends TestCase
{
    /**
     * @param array $subjectData
     * @param Subject|null $findResponse
     * @param Throwable|null $createException
     * @throws AlreadyExistentSubjectException
     * @throws InvalidEntityDataException
     * @throws InvalidEntityException
     *
     * @dataProvider ProvideForTestExecute
     */
    public function testExecute(array $subjectData, ?Subject $findResponse, ?Subject $gotSubject, ?Throwable $createException = null): void
    {
        $repositoryId = 1;
        $subjectRepository = $this->createMock(SubjectsRepositoryInterface::class);
        $subjectRepository->method('find')
            ->with($subjectData['id'], $repositoryId)
            ->willReturn($findResponse);


        if ($createException) {
            $subjectRepository->method('create')
                ->with($gotSubject, $repositoryId)
                ->willThrowException($createException);
            $this->expectException(get_class($createException));
        }

        $subjectCreation = new SubjectCreationUseCase($subjectRepository);
        $subject = $subjectCreation->execute($subjectData, $repositoryId);
        $this->assertEquals($gotSubject, $subject);
    }

    /**
     * @return array
     */
    public function ProvideForTestExecute(): array
    {
        $validSubjectData = [
            'id' => 2,
            'firstName' => 'test',
            'lastName' => 'test',
            'title' => 'Dr',
            'licenseNumber' => '123456',
        ];
        $subject = new Subject(2, 'test', 'test', 'Dr', '123456');
        return [
            [$validSubjectData, null, $subject],
            [$validSubjectData, null, $subject, new InvalidEntityException()],
            [$validSubjectData, null, $subject, new RepositoryInternalException()],
            [['id' => 2], null, $subject, new InvalidEntityDataException(Subject::class, ['id' => 2])],
            [$validSubjectData, $this->createMock(Subject::class), $subject, new AlreadyExistentSubjectException($subject)],
        ];
    }
}
