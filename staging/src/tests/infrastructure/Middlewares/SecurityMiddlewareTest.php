<?php

namespace CMR\Staging\Tests\Infrastructure\Middlewares;

use CMR\Staging\App\Controllers\UnauthorizedException;
use CMR\Staging\App\Security\TokenInfo;
use CMR\Staging\App\Security\TokenManagerInterface;
use CMR\Staging\Infrastructure\Middlewares\SecurityMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SecurityMiddlewareTest extends TestCase
{

    /**
     * @param string $authorizationHeader
     * @param bool $validHeader
     * @param bool $validtoken
     * @throws UnauthorizedException
     * @dataProvider provideForTest__invoke
     */
    public function test__invoke(string $authorizationHeader, bool $validHeader, bool $validtoken)
    {
        if (!$validHeader || !$validtoken) {
            $this->expectException(UnauthorizedException::class);
        }

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeaderLine')
            ->with('Authorization')
            ->willReturn($authorizationHeader);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $tokenManager = $this->createMock(TokenManagerInterface::class);
        if ($validHeader) {
            $validateToken = $tokenManager->expects($this->once())
                ->method('getTokenInfo');
            if (!$validtoken) {
                $validateToken->willThrowException(new UnauthorizedException("Invalid token"));
            } else {
                $validateToken->willReturn(new TokenInfo(1));
                $withRepositoryRequest = $this->createMock(ServerRequestInterface::class);
                $request->expects($this->once())
                    ->method('withAttribute')
                    ->with('repositoryId', 1)
                    ->willReturn($withRepositoryRequest);
                $handler->expects($this->once())
                    ->method('handle')
                    ->with($withRepositoryRequest)
                    ->willReturn($this->createMock(ResponseInterface::class));
            }
        }

        $middleware = new SecurityMiddleware($tokenManager);
        $middleware($request, $handler);
    }

    public function provideForTest__invoke(): array
    {
        return [
            ["Bearer 123456", true, true],
            ["Bearer 123456", true, false],
            ["123456", false, false],
            ["", false, false],
        ];
    }
}
