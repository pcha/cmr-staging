<?php


namespace CMR\Staging\Infrastructure\Adapters;


use CMR\Staging\App\Security\InvalidTokenException;
use CMR\Staging\App\Security\TokenInfo;
use CMR\Staging\App\Security\TokenManagerInterface;

class ArrayTokenManager implements TokenManagerInterface
{
    public function __construct(
        private array $tokens
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getTokenInfo(string $token): TokenInfo
    {
        $tokenArr = $this->tokens[$token] ?? throw new InvalidTokenException();
        return new TokenInfo($tokenArr['repositoryId']);
    }
}