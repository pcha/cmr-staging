<?php

namespace CMR\Staging\Infrastructure\Adapters;

use CMR\Staging\App\Security\InvalidTokenException;
use CMR\Staging\App\Security\TokenInfo;
use PHPUnit\Framework\TestCase;

class ArrayTokenManagerTest extends TestCase
{
    /**
     * @param string $token
     * @param int|null $repositoryId
     * @throws InvalidTokenException
     * @dataProvider provideForTestGetTokenInfo
     */
    public function testGetTokenInfo(string $token, ?int $repositoryId): void
    {
        $tokens = [
            'token1' => [
                'repositoryId' => 1,
            ],
            'token2' => [
                'repositoryId' => 2
            ]
        ];
        $tokenManager = new ArrayTokenManager($tokens);
        if (!$repositoryId) {
            $this->expectException(InvalidTokenException::class);
        }
        $tokenInfo = $tokenManager->getTokenInfo($token);
        if ($repositoryId) {
            $this->assertEquals(new TokenInfo($repositoryId), $tokenInfo);
        }
    }

    public function provideForTestGetTokenInfo(): array
    {
        return [
            ['token1', 1],
            ['nonexistent_token', null],
        ];
    }
}
