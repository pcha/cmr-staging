<?php

namespace CMR\Staging\Tests\App\Consumers;

use Closure;
use CMR\Staging\App\Consumers\ApiClientInterface;
use CMR\Staging\App\Consumers\CoreApiSubjectsRepository;
use CMR\Staging\App\Core\Business\Repositories\InvalidEntityException;
use CMR\Staging\App\Core\Business\Repositories\RepositoryInternalException;
use CMR\Staging\App\Core\Entities\Project;
use CMR\Staging\App\Core\Entities\Subject;
use Exception;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;

class CoreApiSubjectsRepositoryTest extends TestCase
{
    private CoreApiSubjectsRepository $repository;
    private MockObject $client;
    private InvocationMocker $apiCall;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(ApiClientInterface::class);
        $this->repository = new CoreApiSubjectsRepository($this->client);
        $this->apiCall = $this->client
            ->expects($this->once())
            ->method('call');
    }

    /**
     * @param array $apiResponse
     * @param Subject|null $expectedReturn
     * @param string|null $expectedExceptionClass
     * @param ClientExceptionInterface|null $apiException
     * @dataProvider provideForTestFind
     */
    public function testFind(
        int $subjectId = 1,
        array $apiResponse = [],
        ?Subject $expectedReturn = null,
        ?string $expectedExceptionClass = null,
        ?ClientExceptionInterface $apiException = null
    ): void
    {
        $methodCall = fn() => $this->repository->find($subjectId, 2);

        $this->baseTest(
            'get',
            '/repositories/2/subjects',
            $methodCall,
            $apiResponse,
            fn($return) => $this->assertEquals($expectedReturn, $return),
            $expectedExceptionClass,
            $apiException
        );

    }


    public function provideForTestFind(): array
    {
        return [
            [
                'subjectId' => 1,
                'apiResponse' => [200, $this->getTestSubjectDataList()],
                'expectedReturn' => new Subject(1, 'John', 'Doe', 'Dr', '123456'),
            ],
            [
                'subjectId' => 4,
                'apiResponse' => [200, $this->getTestSubjectDataList()],
                'expectedReturn' => null,
            ],
            [
                'subjectId' => 1,
                'apiResponse' => [200, []],
                'expectedReturn' => null,
            ],
            [
                'subjectId' => 1,
                'apiResponse' => [500, 'error'],
                'expectedReturn' => null,
                'expectedExceptionClass' => RepositoryInternalException::class
            ],
            [
                'subjectId' => 1,
                'apiResponse' => [200, ['id' => 1]],
                'expectedReturn' => null,
                'expectedExceptionClass' => RepositoryInternalException::class
            ],
            [
                'subjectId' => 1,
                'apiResponse' => [],
                'expectedReturn' => null,
                'expectedExceptionClass' => RepositoryInternalException::class,
                'apiException' => new class extends Exception implements ClientExceptionInterface {
                },
            ]
        ];
    }

    /**
     * @param array $apiResponse
     * @param string|null $expectedExceptionClass
     * @param ClientExceptionInterface|null $apiException
     * @throws InvalidEntityException
     * @throws RepositoryInternalException
     * @dataProvider provideForTestCreate
     */
    public function testCreate(
        array $apiResponse = [],
        ?string $expectedExceptionClass = null,
        ?ClientExceptionInterface $apiException = null
    ): void
    {
        $this->baseTest(
            'post',
            '/repositories/1/subjects/1',
            fn() => $this->repository->create($this->getTestSubject(), 1),
            $apiResponse,
            null,
            $expectedExceptionClass,
            $apiException
        );
    }

    public function provideForTestCreate(): array
    {
        return [
            [
                'apiResponse' => [201, $this->getTestSubjectData()],
            ],
            [
                'apiResponse' => [400, ['error' => 'error message']],
                'expectedExceptionClass' => InvalidEntityException::class,
            ],
            [
                'apiResponse' => [400, 'Error message'],
                'expectedExceptionClass' => InvalidEntityException::class,
            ],
            [
                'apiResponse' => [500, ['error' => 'error message']],
                'expectedExceptionClass' => RepositoryInternalException::class,
            ],
            [
                'apiResponse' => [500, 'Error message'],
                'expectedExceptionClass' => RepositoryInternalException::class,
            ],
            [
                'apiResponse' => [],
                'expectedExceptionClass' => RepositoryInternalException::class,
                'apiException' => new class extends Exception implements ClientExceptionInterface {
                },
            ]
        ];
    }

    private function getTestSubjectData(): array
    {
        return [
            'id' => 1,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'title' => 'Dr',
            'licenseNumber' => '123456',
        ];
    }

    private function getTestSubjectDataList(): array
    {
        return [
            [
                'id' => 1,
                'firstName' => 'John',
                'lastName' => 'Doe',
                'title' => 'Dr',
                'licenseNumber' => '123456',
            ],
            [
                'id' => 2,
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'title' => 'Dr',
                'licenseNumber' => '111111',
            ]
        ];
    }

    private function getTestSubject(): Subject
    {
        return new Subject(1, 'John', 'Doe', 'Dr', '123456');
    }

    private function baseTest(
        string $httpMethod,
        string $endpoint,
        Closure $methodCall,
        array $apiResponse,
        ?Closure $assertReturn,
        ?string $expectedExceptionClass,
        ?ClientExceptionInterface $apiException
    )
    {
        $this->apiCall
            ->with($httpMethod, $endpoint);
        if ($apiException) {
            $this->apiCall->willThrowException($apiException);
        } else {
            $this->apiCall->willReturn($apiResponse);
        }

        if ($expectedExceptionClass) {
            $this->expectException($expectedExceptionClass);
            $methodCall();
        } else {
            $result = $methodCall();
            if (null !== $assertReturn) {
                $assertReturn($result);
            }
        }
    }

    /**
     * @param array $apiResponse
     * @param array $expectedReturn
     * @param string|null $expectedExceptionClass
     * @param ClientExceptionInterface|null $apiException
     * @throws RepositoryInternalException
     * @dataProvider provideForTestListAssignedProjects
     */
    public function testListAssignedProjects(
        array $apiResponse = [],
        array $expectedReturn = [],
        ?string $expectedExceptionClass = null,
        ?ClientExceptionInterface $apiException = null
    ): void
    {
        $this->baseTest(
            'get',
            '/repositories/1/subjects/1/projects',
            fn() => $this->repository->listAssignedProjects(1, 1),
            $apiResponse,
            fn($result) => $this->assertEquals($expectedReturn, $result),
            $expectedExceptionClass,
            $apiException
        );
    }

    public function provideForTestListAssignedProjects(): array
    {
        return [
            [
                'apiResponse' => [200, []],
                'expectedReturn' => [],
            ],
            [
                'apiResponse' => [200, [
                    ['id' => 1],
                    ['id' => 2],
                ]],
                'expectedReturn' => [new Project(1), new Project(2)],
            ],
            [
                'apiResponse' => [500, ['error' => 'Error message']],
                'expectedReturn' => [],
                'expectedException' => RepositoryInternalException::class,
            ],
            [
                'apiResponse' => [500, 'Error message'],
                'expectedReturn' => [],
                'expectedException' => RepositoryInternalException::class,
            ],
            [
                'apiResponse' => [],
                'expectedReturn' => [],
                'expectedException' => RepositoryInternalException::class,
                'apiException' => new class extends Exception implements ClientExceptionInterface {
                },
            ],
        ];
    }

    /**
     * @param array $apiResponse
     * @param string|null $expectedExceptionClass
     * @param ClientExceptionInterface|null $apiException
     * @dataProvider provideForTestAssignProject
     */
    public function testAssignProject(
        array $apiResponse = [],
        ?string $expectedExceptionClass = null,
        ?ClientExceptionInterface $apiException = null
    ): void
    {
        $this->baseTest(
            'post',
            '/repositories/3/subjects/1/projects/2',
            fn() => $this->repository->assignProject(1, 2, 3),
            $apiResponse,
            null,
            $expectedExceptionClass,
            $apiException
        );
    }

    public function provideForTestAssignProject(): array
    {
        return [
            [
                'apiResponse' => [200, ['message' => 'ok']]
            ],
            [
                'apiResponse' => [500, ['error' => 'error message']],
                'expectedExceptionClass' => RepositoryInternalException::class,
            ],
            [
                'apiResponse' => [500, 'Error message'],
                'expectedExceptionClass' => RepositoryInternalException::class,
            ],
            [
                'apiResponse' => [],
                'expectedExceptionClass' => RepositoryInternalException::class,
                'apiException' => new class extends Exception implements ClientExceptionInterface {
                },
            ]
        ];
    }
}
