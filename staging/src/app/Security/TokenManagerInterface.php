<?php

namespace CMR\Staging\App\Security;

interface TokenManagerInterface
{
    /**
     * @param string $token
     * @return TokenInfo
     * @throws InvalidTokenException
     */
    public function getTokenInfo(string $token): TokenInfo;
}