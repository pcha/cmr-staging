<?php

namespace CMR\Staging\Tests\App\Controllers;

use Closure;
use CMR\Staging\App\Controllers\BadGatewayException;
use CMR\Staging\App\Controllers\BadRequestException;
use CMR\Staging\App\Controllers\ForbidenException;
use CMR\Staging\App\Controllers\NotFoundException;
use CMR\Staging\App\Controllers\SubjectsController;
use CMR\Staging\App\Core\Business\Repositories\RepositoryInternalException;
use CMR\Staging\App\Core\Business\UseCases\AlreadyExistentSubjectException;
use CMR\Staging\App\Core\Business\UseCases\InvalidEntityDataException;
use CMR\Staging\App\Core\Business\UseCases\ProjectAlreadyAssignedException;
use CMR\Staging\App\Core\Business\UseCases\SubjectCreationUseCase as SubjectCreationUC;
use CMR\Staging\App\Core\Business\UseCases\SubjectNotFoundException;
use CMR\Staging\App\Core\Business\UseCases\SubjectProjectAssignationUseCase as ProjectAssignationUC;
use CMR\Staging\App\Core\Entities\Subject;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Response;

class SubjectsControllerTest extends TestCase
{
    /**
     * @var SubjectsController
     */
    private SubjectsController $controller;

    /**
     * @var MockObject[]
     */
    private array $dependenciesMocks;

    private int $repositoryId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dependenciesMocks = [
            SubjectCreationUC::class => $this->createMock(SubjectCreationUC::class),
            ProjectAssignationUC::class => $this->createMock(ProjectAssignationUC::class),
            Request::class => $this->createMock(Request::class),
        ];
        $this->controller = new SubjectsController(
            $this->dependenciesMocks[SubjectCreationUC::class],
            $this->dependenciesMocks[ProjectAssignationUC::class],
        );
        $this->repositoryId = 1;
        $this->dependenciesMocks[Request::class]->method('getAttribute')
            ->with('repositoryId')
            ->willReturn($this->repositoryId);
    }

    /**
     * @param Closure $createSubjectResult
     * @param int $expectedStatusCode
     * @param string $expectedBody
     * @dataProvider provideForTestCreate
     */
    public function testCreate(Closure $createSubjectResult, int $expectedStatusCode, string $expectedBody, string $expectedException = "")
    {
        $subjectData = [
            'id' => 1,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'title' => 'Dr',
            'licenseNumber' => '123456',
        ];
        $requestJson = <<<JSON
{
    "firstName": "John",
    "lastName": "Doe",
    "title": "Dr",
    "licenseNumber": "123456"
}
JSON;
        $requestBody = $this->createMock(StreamInterface::class);
        $requestBody->method('getContents')
            ->willReturn($requestJson);
        $request = $this->dependenciesMocks[Request::class];
        $request->method('getBody')
            ->willReturn($requestBody);

        $createSubject = $this->dependenciesMocks[SubjectCreationUC::class]
            ->expects($this->once())
            ->method('execute')
            ->with($subjectData);
        $createSubjectResult($createSubject);

        $response = new Response();
        $action = fn() => $this->controller->create($request, $response, ['id' => '1']);
        if ($expectedException) {
            $this->expectException($expectedException);
            $action();
        } else {
            $response = $action();
            $response->getBody()->rewind();

            $this->assertEquals($expectedStatusCode, $response->getStatusCode());
            $this->assertContains('application/json', $response->getHeader('Content-Type'));
            $responseContents = $response->getBody()->getContents();
            $this->assertJson($responseContents);
            $this->assertJsonStringEqualsJsonString($expectedBody, $responseContents);
        }
    }

    /**
     * @return array[]
     */
    public function provideForTestCreate(): array
    {
        $subject = new Subject(1, 'John', 'Doe', 'Dr', '123456');
        $existentSubjectException = new AlreadyExistentSubjectException($subject);
        $invalidSubjectDataException = new InvalidEntityDataException(Subject::class, []);
        return [
            [
                fn(InvocationMocker $createSubject) => $createSubject->willReturn($subject),
                201,
                <<<JSON
{
    "id": 1,
    "firstName": "John",
    "lastName": "Doe",
    "title": "Dr",
    "licenseNumber": "123456"
}
JSON
            ],
            [
                fn(InvocationMocker $createSubject) => $createSubject->willThrowException($existentSubjectException),
                0,
                "",
                ForbidenException::class
            ],
            [
                fn(InvocationMocker $createSubject) => $createSubject->willThrowException($invalidSubjectDataException),
                0,
                json_encode(['error' => $invalidSubjectDataException->getMessage()]),
                BadRequestException::class
            ],
            [
                fn(InvocationMocker $createSubject) => $createSubject->willThrowException(new RepositoryInternalException()),
                0,
                "",
                BadGatewayException::class,
            ],
        ];
    }

    /**
     * @param string $subjectId
     * @param string $requestBody
     * @param Closure|null $setAssignProjectResult
     * @param string|null $expectedException
     * @dataProvider provideForTestAssignProject
     */
    public function testAssignProject(
        string $subjectId,
        string $requestBody,
        ?Closure $setAssignProjectResult,
        ?string $expectedException = null
    ): void
    {
        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $projectId = 2;
        $args = [
            'id' => $subjectId,
        ];

        $requestBodyStream = $this->createMock(StreamInterface::class);
        $requestBodyStream->method('getContents')
            ->willReturn($requestBody);
        $request = $this->dependenciesMocks[Request::class];
        $request
            ->method('getBody')
            ->willReturn($requestBodyStream);

        if ($setAssignProjectResult) {
            $assignProject = $this->dependenciesMocks[ProjectAssignationUC::class]
                ->expects($this->once())
                ->method('execute')
                ->with($subjectId, $projectId, $this->repositoryId);
            $setAssignProjectResult($assignProject);
        }


        $response = new Response();
        $response = $this->controller->assignProject($request, $response, $args);
        $response->getBody()->rewind();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('application/json', $response->getHeader('Content-Type'));
        $this->assertJson($response->getBody()->getContents());
    }

    public function provideForTestAssignProject(): array
    {
        $projectAlreadyAssigned = new ProjectAlreadyAssignedException(2, 1);
        return [
            [
                '1',
                '{"projectId": 2}',
                fn(InvocationMocker $assignProject) => $assignProject,
            ],
            [
                '1',
                '{"projectId": 2}',
                fn(InvocationMocker $assignProject) => $assignProject->willThrowException($projectAlreadyAssigned),
                ForbidenException::class,
            ],
            [
                '1',
                '{"projectId": 2}',
                fn(InvocationMocker $assignProject) => $assignProject->willThrowException(new SubjectNotFoundException(1)),
                NotFoundException::class,
            ],
            [
                '1',
                '{"projectId": 2}',
                fn(InvocationMocker $assignProject) => $assignProject->willThrowException(new RepositoryInternalException("error")),
                BadGatewayException::class,
            ],
            [
                'asd',
                '{"projectId": 2}',
                null,
                BadRequestException::class,
            ],
            [
                '1',
                '{"projectId": "2"}',
                null,
                BadRequestException::class,
            ],
            [
                '1',
                '{"project_id": 2}',
                null,
                BadRequestException::class,
            ],
        ];
    }
}
