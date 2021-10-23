<?php


namespace CMR\Staging\Infrastructure\Adapters;


use CMR\Staging\App\Consumers\ApiClientInterface;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;

class GuzzleApiClient implements ApiClientInterface
{
    public function __construct(
        private GuzzleClientInterface $client
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function call(string $method, string $url, array $headers = [], mixed $body = ""): array
    {
        if (!is_string($body)) {
            $body = json_encode($body);
        }
        if (null !== json_decode($body)) {
            $headers['Content-Type'] = 'application/json';
        }
        $headers['Accept'] = 'application/json';
        $response = $this->client->request($method, $url, ['headers' => $headers, 'body' => $body]);
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        return [$statusCode, json_decode($body, true) ?? $body];
    }
}