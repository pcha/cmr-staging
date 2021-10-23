<?php

namespace CMR\Staging\Tests\Infrastructure\Middlewares;

use CMR\Staging\Infrastructure\Middlewares\CorsMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class CorsMiddlewareTest extends TestCase
{
    public function test__invoke()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $requestHandler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());
        $middleware = new CorsMiddleware();
        $response = $middleware($request, $requestHandler);
        $this->assertEquals('*', $response->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertEquals('X-Requested-With, Content-Type, Accept, Origin, Authorization', $response->getHeaderLine('Access-Control-Allow-Headers'));
        $this->assertEquals('GET, POST, PUT, DELETE, PATCH, OPTIONS', $response->getHeaderLine('Access-Control-Allow-Methods'));
    }
}
