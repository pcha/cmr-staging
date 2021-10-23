<?php


namespace CMR\Staging\IntegrationTests;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;

class SubjectsTest extends TestCase
{
    const VALID_TOKEN = 'Bearer df3e6b0bb66ceaadca4f84cbc371fd66e04d20fe51fc414da8d1b84d31d178de';
    private ClientInterface $client;

    protected function setUp(): void
    {
        parent::setUp();
        $staging_host = getenv('staging_host') ?: 'http://staging';
        $this->client = new Client(['base_uri' => $staging_host]);
    }

    /**
     * @param int $subjectId
     * @param int $expectedStatusCode
     * @param string $expectedBody
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @covers       PUT /subjects/{id} (Create Subject)
     * @dataProvider provideForTestCreate
     */
    public function testCreate(int $subjectId, int $expectedStatusCode, ?string $expectedBody): void
    {
        try {
            $response = $this->client->put("/subjects/{$subjectId}", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => self::VALID_TOKEN
                ],
                'body' => json_encode([
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'title' => 'Dr',
                    'licenseNumber' => '123456',
                ])
            ]);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
        if ($expectedBody) {
            $this->assertJsonStringEqualsJsonString($expectedBody, $response->getBody()->getContents());
        }
    }

    /**
     * @return array[]
     */
    public function provideForTestCreate(): array
    {
        return [
            [
                'subjectId' => 3,
                'expectedStatusCode' => 201,
                'expectedBody' => json_encode([
                    'id' => 3,
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'title' => 'Dr',
                    'licenseNumber' => '123456',
                ]),
            ],
            [
                'subjectId' => 1,
                'expectedStatusCode' => 403,
                'expectedBody' => null,
            ],
        ];
    }

    /**
     * @param int $subjectId
     * @param int $projectId
     * @param int $expectedStatuscode
     * @throws GuzzleException
     * @covers       POST /subjects/{id}/assign (Assign project to subject)
     * @dataProvider provideForTestAssignProject
     */
    public function testAssignProject(int $subjectId, int $projectId, int $expectedStatuscode): void
    {
        try {
            $response = $this->client->post("/subjects/$subjectId/assign", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => self::VALID_TOKEN
                ],
                'body' => json_encode([
                    'projectId' => $projectId
                ]),
            ]);
        } catch (RequestException $e) {
            $response = $e->getResponse();
        }
        $this->assertEquals($expectedStatuscode, $response->getStatusCode());
    }

    public function provideForTestAssignProject(): array
    {
        return [
            [
                'subjectId' => 1,
                'projectId' => 3,
                'expectedStatusCode' => 200,
            ],
            [
                'subjectId' => 3,
                'projectId' => 3,
                'expectedStatusCode' => 404,
            ],
            [
                'subjectId' => 1,
                'projectId' => 2,
                'expectedStatusCode' => 403,
            ],
        ];
    }

    /**
     * @param string $authorization
     * @param $invalidToken
     * @throws GuzzleException
     * @covers       The token validation
     * @dataProvider provideForTestAuthorization
     */
    public function testAuthorization(?string $authorization, $invalidToken): void
    {
        $headers = $authorization ? ['Authorization' => $authorization] : [];
        try {
            $this->client->get('/ping', ['headers' => $headers]);
        } catch (RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            if ($invalidToken) {
                $this->assertEquals(401, $statusCode);
            } else {
                $this->assertNotEquals(401, $statusCode);
            }
        }
    }

    /**
     * @return array
     */
    public function provideForTestAuthorization(): array
    {
        return [
            [
                'authorization' => self::VALID_TOKEN,
                'invalidToken' => false
            ],
            [
                'authorization' => 'Bearer invalidtoken',
                'invalidToken' => true,
            ],
            [
                'authorization' => null,
                'invalidToken' => true,
            ],
        ];
    }
}