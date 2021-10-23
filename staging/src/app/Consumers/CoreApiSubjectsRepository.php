<?php


namespace CMR\Staging\App\Consumers;


use CMR\Staging\App\Core\Business\Repositories\InvalidEntityException;
use CMR\Staging\App\Core\Business\Repositories\RepositoryInternalException;
use CMR\Staging\App\Core\Business\Repositories\SubjectsRepositoryInterface;
use CMR\Staging\App\Core\Entities\Project;
use CMR\Staging\App\Core\Entities\Subject;
use Psr\Http\Client\ClientExceptionInterface;
use Throwable;

class CoreApiSubjectsRepository implements SubjectsRepositoryInterface
{
    public function __construct(
        private ApiClientInterface $client,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function find($id, int $repositoryId): ?Subject
    {
        try {
            [$statusCode, $body] = $this->client->call('get', "/repositories/$repositoryId/subjects");

        } catch (ClientExceptionInterface $e) {
            throw new RepositoryInternalException($e->getMessage(), $e);
        }
        return match ($statusCode) {
            200 => $this->findSubjectInBody($id, $body),
            404 => null,
            default => throw new RepositoryInternalException(
                "Status code $statusCode received with body'" . is_string($body) ? $body : json_encode($body) . "'"
            ),
        };
    }

    private function findSubjectInBody($id, $body): ?Subject
    {
        try {
            foreach ($body as $el) {
                if ($id == $el['id']) return $this->getSubjectFromBody($el);
            }
        } catch (Throwable $e) {
            throw new RepositoryInternalException("It can't convert body to Subject", $e);
        }
        return null;
    }

    /**
     * @param array $body
     * @return Subject
     * @throws RepositoryInternalException
     */
    private function getSubjectFromBody(array $body): Subject
    {
        try {
            return new Subject(
                $body['id'],
                $body['firstName'],
                $body['lastName'],
                $body['title'],
                $body['licenseNumber'],
            );
        } catch (Throwable $e) {
            throw new RepositoryInternalException("It can't convert body to Subject", $e);
        }
    }

    /**
     * @param Subject $subject
     * @return array{firstName: string, lastName: string, title: string, licenseNumber: string}
     */
    private function getBodyFromSubject(Subject $subject): array
    {
        return [
            'firstName' => $subject->getFirstName(),
            'lastName' => $subject->getLastName(),
            'title' => $subject->getTitle(),
            'licenseNumber' => $subject->getLicenseNumber(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function create(Subject $subject, int $repositoryId): void
    {
        try {
            $subjectData = $this->getBodyFromSubject($subject);
            [$statusCode, $body] = $this->client->call(
                'post',
                "/repositories/$repositoryId/subjects/{$subject->getId()}",
                body: $subjectData
            );
        } catch (ClientExceptionInterface $e) {
            throw new RepositoryInternalException($e->getMessage(), $e);
        }
        match ($statusCode) {
            201 => null,
            400 => throw new  InvalidEntityException($body['error'] ?? "Invalid subject Data: " . json_encode($subjectData)),
            default => throw new RepositoryInternalException(
                "Status code $statusCode received with body'" . (is_string($body) ? $body : json_encode($body)) . "'"
            ),
        };
    }


    /**
     * @inheritDoc
     */
    public function listAssignedProjects(int $subjectId, int $repositoryId): array
    {
        try {
            [$statusCode, $body] = $this->client->call('get', "/repositories/$repositoryId/subjects/$subjectId/projects");
        } catch (ClientExceptionInterface $e) {
            throw new RepositoryInternalException($e->getMessage(), $e);
        }
        $parse = function ($body) {
            try {
                return array_map(fn($proj) => new Project($proj['id']), $body);
            } catch (Throwable $e) {
                throw new RepositoryInternalException("It couldn't parse response: " . json_encode($body), $e);
            }
        };
        return match ($statusCode) {
            200 => $parse($body),
            default => throw new RepositoryInternalException(
                "Status code $statusCode received with body'" . (is_string($body) ? $body : json_encode($body)) . "'"
            ),
        };
    }

    /**
     * @inheritDoc
     */
    public function assignProject(int $subjectId, int $projectId, int $repositoryId): void
    {
        try {
            [$statusCode, $body] = $this->client->call('post', "/repositories/$repositoryId/subjects/$subjectId/projects/$projectId");
        } catch (ClientExceptionInterface $e) {
            throw new RepositoryInternalException($e->getMessage(), $e);
        }
        match ($statusCode) {
            200 => null,
            default => throw new RepositoryInternalException(
                "Status code $statusCode received with body'" . (is_string($body) ? $body : json_encode($body)) . "'"
            ),
        };
    }
}