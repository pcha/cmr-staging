<?php

namespace CMR\Staging\App\Controllers;

use CMR\Staging\App\Core\Business\Repositories\InvalidEntityException;
use CMR\Staging\App\Core\Business\Repositories\RepositoryInternalException;
use CMR\Staging\App\Core\Business\UseCases\AlreadyExistentSubjectException;
use CMR\Staging\App\Core\Business\UseCases\InvalidEntityDataException;
use CMR\Staging\App\Core\Business\UseCases\ProjectAlreadyAssignedException;
use CMR\Staging\App\Core\Business\UseCases\SubjectCreationUseCase;
use CMR\Staging\App\Core\Business\UseCases\SubjectNotFoundException;
use CMR\Staging\App\Core\Business\UseCases\SubjectProjectAssignationUseCase;
use CMR\Staging\App\Core\Entities\Subject;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class SubjectsController
 * @package CMR\Staging\App\Controllers
 */
class SubjectsController
{
    /**
     * SubjectsController constructor.
     * @param SubjectCreationUseCase $subjectCreation
     * @param SubjectProjectAssignationUseCase $subjectProjectAssignation
     * @OA\Put(
     *     path="/subjects/{id}",
     *     summary="create new Subject".
     *     @OA\Request
     * )
     */
    public function __construct(
        private SubjectCreationUseCase $subjectCreation,
        private SubjectProjectAssignationUseCase $subjectProjectAssignation
    )
    {
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws ForbidenException
     * @throws BadRequestException
     * @throws BadGatewayException
     * @throws InvalidEntityException
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        $repositoryId = $request->getAttribute('repositoryId');
        $reqBody = $request->getBody()->getContents();
        $subjectData = json_decode($reqBody, true);
        $subjectData['id'] = intval($args['id']);
        $statusCode = 201;
        try {
            $subject = $this->subjectCreation->execute($subjectData, $repositoryId);
            $respBody = $this->getSubjectAsJsonArr($subject);
        } catch (AlreadyExistentSubjectException $e) {
            throw new ForbidenException($e->getMessage(), $e);
        } catch (InvalidEntityDataException $e) {
            throw new BadRequestException($e->getMessage(), $e);
        } catch (RepositoryInternalException $e) {
            throw new BadGatewayException($e);
        }
        $response->getBody()
            ->write(json_encode($respBody));
        return $response
            ->withStatus($statusCode)
            ->withAddedHeader('Content-Type', 'application/json');
    }

    private function getSubjectAsJsonArr(Subject $subject): array
    {
        return [
            'id' => $subject->getId(),
            'firstName' => $subject->getFirstName(),
            'lastName' => $subject->getLastName(),
            'title' => $subject->getTitle(),
            'licenseNumber' => $subject->getLicenseNumber(),
        ];
    }

    /**
     * @param array $args
     * @return int
     * @throws BadRequestException
     */
    protected function getId(array $args): int
    {
        $id = $args['id'] ?? null;
        if (!ctype_digit($id)) {
            throw new BadRequestException('The subject id must be numeric');
        }
        return intval($id);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws BadRequestException
     * @throws ForbidenException
     * @throws NotFoundException
     * @throws BadGatewayException
     */
    public function assignProject(Request $request, Response $response, array $args): Response
    {
        $subjectId = $this->getId($args);
        $repositoryId = $request->getAttribute('repositoryId');
        $reqBody = $request->getBody()->getContents();
        $projectId = json_decode($reqBody, true)['projectId'] ?? null;
        if (!is_int($projectId)) {
            throw new BadRequestException('Invalid "projectId" property value: ' . print_r($projectId, true) . '. Integer was expected');
        }

        try {
            $this->subjectProjectAssignation->execute($subjectId, $projectId, $repositoryId);
        } catch (SubjectNotFoundException $e) {
            throw new NotFoundException($e->getMessage(), $e);
        } catch (ProjectAlreadyAssignedException $e) {
            throw new ForbidenException($e->getMessage(), $e);
        } catch (RepositoryInternalException $e) {
            throw new BadGatewayException($e);
        }

        $response->getBody()->write(json_encode(['message' => "Project $projectId assigned to subject $subjectId"]));
        return $response->withAddedHeader('Content-Type', 'application/json');
    }
}