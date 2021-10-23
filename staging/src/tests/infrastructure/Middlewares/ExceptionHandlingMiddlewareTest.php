<?php

namespace CMR\Staging\Tests\Infrastructure\Middlewares;

use CMR\Staging\App\Controllers\BadRequestException;
use CMR\Staging\App\Controllers\ForbidenException;
use CMR\Staging\Infrastructure\Middlewares\ExceptionHandlingMiddleware;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;
use Throwable;

class ExceptionHandlingMiddlewareTest extends TestCase
{

    /**
     * @param Throwable|null $exception
     * @param int $expectedStatusCode
     * @param bool $exceptionMsgAsBody
     * @dataProvider provideForTest__invoke
     */
    public function test__invoke(?Throwable $exception, int $expectedStatusCode = 0, bool $exceptionMsgAsBody = false): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        $handleFunction = $requestHandler->expects($this->once())
            ->method('handle');
        $middleware = new ExceptionHandlingMiddleware($this->createMock(LoggerInterface::class));
        $invoke = fn() => $middleware($request, $requestHandler);
        if (!$exception) {
            $response = new Response();
            $handleFunction->willReturn($response);
            $this->assertSame($response, $invoke());
            return;
        }
        $handleFunction->willThrowException($exception);
        $response = $invoke();

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
        $exceptionJson = "{\"error\":\"{$exception->getMessage()}\"}";
        $response->getBody()->rewind();
        $body = $response->getBody()->getContents();
        if ($exceptionMsgAsBody) {
            $this->assertJsonStringEqualsJsonString($exceptionJson, $body);
        } else {
            $this->assertJsonStringNotEqualsJsonString($exceptionJson, $body);
        }
        $this->assertContains('application/json', $response->getHeader('Content-Type'));
    }

    public function provideForTest__invoke(): array
    {
        return [
            [null],
            [
                new BadRequestException("Bad request message"),
                400,
                true
            ],
            [
                new ForbidenException("Forbiden message"),
                403,
                true
            ],
            [
                new Exception("Exception message"),
                500,
                false
            ]
        ];
    }
}
