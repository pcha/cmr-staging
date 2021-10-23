<?php

namespace CMR\Staging\App\Security;


class TokenInfo
{
    public function __construct(
        private int $repositoryId
    )
    {
    }

    /**
     * @return int
     */
    public function getRepositoryId(): int
    {
        return $this->repositoryId;
    }
}