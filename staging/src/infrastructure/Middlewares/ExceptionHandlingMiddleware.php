<?php

namespace CMR\Staging\Infrastructure\Middlewares;

use CMR\Staging\App\Controllers\AbstractHttpException as HttpExceptionAlias;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;
use Throwable;

class ExceptionHandlingMiddleware implements MiddlewareInterface
{

    public function __construct(
        private LoggerInterface $logger
    )
    {
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpExceptionAlias $e) {
            $bodyArr = [
                'error' => $e->getMessage(),
            ];
            $statusCode = $e->getCode();
            $logLevel = $e->getCode() >= 500 ? 'error' : 'info';

            $this->logger->log($logLevel, $this->getExceptionMessage($e));
        } catch (Throwable $e) {
            $this->logger->error($this->getExceptionMessage($e));
            $bodyArr = [
                'error' => "Unexpected error (code {$e->getCode()})"
            ];
            $statusCode = 500;
        }
        $response = new Response();
        $response->getBody()->write(json_encode($bodyArr));
        return $response->withStatus($statusCode)
            ->withAddedHeader('Content-Type', 'application/json');
    }

    private function getExceptionMessage(Throwable $e): string
    {
        $msg = get_class($e) . ": {$e->getMessage()} on {$e->getFile()}:{$e->getLine()}";
        if ($e->getPrevious()) {
            $msg .= " caused by {$this->getExceptionMessage($e->getPrevious())}";
        } else {
            $msg .= " With Trace: {$e->getTraceAsString()}";
        }
        return $msg;
    }
}