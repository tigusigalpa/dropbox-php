<?php

namespace Tigusigalpa\Dropbox\Tests;

use PHPUnit\Framework\TestCase;
use Tigusigalpa\Dropbox\DropboxClient;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

class DropboxClientTest extends TestCase
{
    private DropboxClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $accessToken = getenv('DROPBOX_ACCESS_TOKEN') ?: 'test_token';
        $this->client = new DropboxClient($accessToken);
    }

    public function testClientInstantiation()
    {
        $this->assertInstanceOf(DropboxClient::class, $this->client);
    }

    public function testGetAccessToken()
    {
        $token = $this->client->getAccessToken();
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testSetAccessToken()
    {
        $newToken = 'new_test_token';
        $this->client->setAccessToken($newToken);
        $this->assertEquals($newToken, $this->client->getAccessToken());
    }

    public function testGetAuthorizationUrl()
    {
        $url = DropboxClient::getAuthorizationUrl(
            'test_client_id',
            'https://example.com/callback',
            'test_state',
            ['files.content.read', 'files.content.write']
        );

        $this->assertStringContainsString('https://www.dropbox.com/oauth2/authorize', $url);
        $this->assertStringContainsString('client_id=test_client_id', $url);
        $this->assertStringContainsString('redirect_uri=', $url);
        $this->assertStringContainsString('state=test_state', $url);
        $this->assertStringContainsString('response_type=code', $url);
    }

    public function testFilesEndpointExists()
    {
        $this->assertObjectHasProperty('files', $this->client);
    }

    public function testSharingEndpointExists()
    {
        $this->assertObjectHasProperty('sharing', $this->client);
    }

    public function testUsersEndpointExists()
    {
        $this->assertObjectHasProperty('users', $this->client);
    }

    public function testFileRequestsEndpointExists()
    {
        $this->assertObjectHasProperty('fileRequests', $this->client);
    }

    public function testPaperEndpointExists()
    {
        $this->assertObjectHasProperty('paper', $this->client);
    }

    public function testCheckEndpointExists()
    {
        $this->assertObjectHasProperty('check', $this->client);
    }
}
