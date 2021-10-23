<?php


namespace CMR\Staging\Config;


use CMR\Staging\App\Consumers\ApiClientInterface;
use CMR\Staging\App\Consumers\CoreApiSubjectsRepository;
use CMR\Staging\App\Core\Business\Repositories\SubjectsRepositoryInterface;
use CMR\Staging\App\Security\TokenManagerInterface;
use CMR\Staging\Infrastructure\Adapters\ArrayTokenManager;
use CMR\Staging\Infrastructure\Adapters\GuzzleApiClient;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Slim\Logger;
use function DI\create;
use function DI\env;
use function DI\get;

class Dependencies
{
    public static function getDefinitions(): array
    {
        return [
            ApiClientInterface::class => create(GuzzleApiClient::class)
                ->constructor(create(Client::class)
                    ->constructor(['base_uri' => env('core_host')])),
            SubjectsRepositoryInterface::class => get(CoreApiSubjectsRepository::class),
            TokenManagerInterface::class => create(ArrayTokenManager::class)
                ->constructor(TokensProvider::provide()),
            LoggerInterface::class => get(Logger::class)
        ];
    }
}