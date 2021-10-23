<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy as Group;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();


$app->group('/repositories/{repositoryId:[0-9]+}', function (Group $repository_group) {
    $repository_group->group('/subjects', function (Group $subjects_group) {
        $subjects_group->get('', function (Request $request, Response $response, $args) {
            $response->getBody()->write(json_encode([
                [
                    'id' => 1,
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'title' => 'Dr',
                    'licenseNumber' => '123456',
                ],
                [
                    'id' => 1,
                    'firstName' => 'Jane',
                    'lastName' => 'Doe',
                    'title' => 'Dr',
                    'licenseNumber' => '111111',
                ]
            ]));
            return $response->withAddedHeader('Content-Type', 'application/json');
        });
        $subjects_group->group('/{subjectId:[0-9]+}', function (Group $specific_subject_group) {
            $specific_subject_group->post('', function (Request $request, Response $response, $args) {
                $response->getBody()->write(
                    json_encode(
                        array_merge(
                            json_decode(
                                $request
                                    ->getBody()
                                    ->getContents(),
                                true
                            ),
                            ['id' => $args['subjectId']]
                        )
                    )
                );
                return $response->withAddedHeader('Content-Type', 'application/json')
                    ->withStatus(201);
            });
            $specific_subject_group->group('/projects', function (Group $group) {
                $group->get('', function (Request $request, Response $response, array $args) {
                    $response->getBody()->write(json_encode(
                        [
                            [
                                'id' => 1,
                                'name' => 'Project 1'
                            ],
                            [
                                'id' => 2,
                                'name' => 'Project 2'
                            ],
                        ]
                    ));
                    return $response->withAddedHeader('Content-Type', 'application/json');
                });
                $group->post('/{projectId:[0-9]+}', function (Request $request, Response $response, $args) {
                    $response->getBody()->write(json_encode(['message' => 'ok']));
                    return $response->withAddedHeader('Content-Type', 'application/json');
                });
            });
        });
    });
});

$app->run();