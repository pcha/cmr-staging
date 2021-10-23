<?php

namespace CMR\Staging\Infrastructure\Middlewares;

use CMR\Staging\App\Controllers\UnauthorizedException;
use CMR\Staging\App\Security\InvalidTokenException;
use CMR\Staging\App\Security\TokenManagerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SecurityMiddleware implements MiddlewareInterface
{
    public function __construct(
        private TokenManagerInterface $tokenManager
    )
    {
    }

    /**
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     * @throws UnauthorizedException
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authorization = $request->getHeaderLine('Authorization');
        if (!$authorization) {
            throw new UnauthorizedException("Authorization token not provided");
        }
        $parts = explode(' ', $authorization);
        if (2 == count($parts)) {
            [$type, $token] = $parts;
            if ('Bearer' == $type) {
                try {
                    $tokenInfo = $this->tokenManager->getTokenInfo($token);
                    return $handler->handle($request
                        ->withAttribute('repositoryId', $tokenInfo->getRepositoryId()));
                } catch (InvalidTokenException $e) {
                    throw new UnauthorizedException($e->getMessage(), $e);
                }
            }
        }
        throw new UnauthorizedException("Invalid authentication method");
    }
}