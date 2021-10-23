<?php

namespace CMR\Staging\Infrastructure\Adapters;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class GuzzleApiClientTest extends TestCase
{
    /**
     * @param array $originalHeaders
     * @param array $finalHeaders
     * @param mixed $origReqBody
     * @param string $finalReqBody
     * @param string $respBodyStr
     * @param string $returnedBody
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @dataProvider provideForTestCall
     */
    public function testCall(
        array $originalHeaders,
        array $finalHeaders,
        mixed $origReqBody,
        mixed $finalReqBody,
        string $respBodyStr,
        mixed $returnedBody,
    )
    {
        $method = 'method';
        $url = 'url';
        $statusCode = 200;
        $responseBody = $this->createMock(StreamInterface::class);
        $responseBody->method('getContents')
            ->willReturn($respBodyStr);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')
            ->willReturn($responseBody);
        $response->method('getStatusCode')
            ->willReturn($statusCode);
        $client = $this->createMock(GuzzleClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with($method, $url, ['headers' => $finalHeaders, 'body' => $finalReqBody])
            ->willReturn($response);
        $adapter = new GuzzleApiClient($client);
        $return = $adapter->call($method, $url, $originalHeaders, $origReqBody);
        $this->assertEquals([$statusCode, $returnedBody], $return);
    }

    public function provideForTestCall()
    {
        return [
            [
                'originalHeaders' => [],
                'finalHeaders' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'origReqBody' => [
                    'key' => 'value'
                ],
                'finalReqBody' => '{"key":"value"}',
                'respBodyStr' => '{"key":"value"}',
                'returnedBody' => [
                    'key' => 'value',
                ],
            ],
            [
                'originalHeaders' => [],
                'finalHeaders' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'origReqBody' => '{"key":"value"}',
                'finalReqBody' => '{"key":"value"}',
                'respBodyStr' => '{"key":"value"}',
                'returnedBody' => [
                    'key' => 'value',
                ],
            ],
            [
                'originalHeaders' => [
                    'Some-Key' => 'some value',
                ],
                'finalHeaders' => [
                    'Some-Key' => 'some value',
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'origReqBody' => [
                    'key' => 'value'
                ],
                'finalReqBody' => '{"key":"value"}',
                'respBodyStr' => '{"key":"value"}',
                'returnedBody' => [
                    'key' => 'value',
                ],
            ],
            [
                'originalHeaders' => [
                ],
                'finalHeaders' => [
                    'Accept' => 'application/json',
                ],
                'origReqBody' => '',
                'finalReqBody' => '',
                'respBodyStr' => '{"key":"value"}',
                'returnedBody' => [
                    'key' => 'value',
                ],
            ],
            [
                'originalHeaders' => [],
                'finalHeaders' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'origReqBody' => [
                    'key' => 'value'
                ],
                'finalReqBody' => '{"key":"value"}',
                'respBodyStr' => 'some text',
                'returnedBody' => 'some text',
            ],
        ];
    }
}
