<?php


namespace CMR\Staging\App\Consumers;


use Psr\Http\Client\ClientExceptionInterface;

interface ApiClientInterface
{
    /**
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param mixed|string $body
     * @return array [int statusCode, array|string Response]
     * @throws ClientExceptionInterface
     */
    public function call(string $method, string $url, array $headers = [], mixed $body = ""): array;
}