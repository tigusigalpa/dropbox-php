<?php

namespace Tigusigalpa\Dropbox\Tests;

use PHPUnit\Framework\TestCase;
use Tigusigalpa\Dropbox\DropboxClient;
use Tigusigalpa\Dropbox\Endpoints\Files;

class FilesEndpointTest extends TestCase
{
    private DropboxClient $client;
    private Files $files;

    protected function setUp(): void
    {
        parent::setUp();
        
        $accessToken = getenv('DROPBOX_ACCESS_TOKEN') ?: 'test_token';
        $this->client = new DropboxClient($accessToken);
        $this->files = $this->client->files;
    }

    public function testFilesEndpointInstantiation()
    {
        $this->assertInstanceOf(Files::class, $this->files);
    }

    public function testUploadMethodExists()
    {
        $this->assertTrue(method_exists($this->files, 'upload'));
    }

    public function testDownloadMethodExists()
    {
        $this->assertTrue(method_exists($this->files, 'download'));
    }

    public function testListFolderMethodExists()
    {
        $this->assertTrue(method_exists($this->files, 'listFolder'));
    }

    public function testCreateFolderMethodExists()
    {
        $this->assertTrue(method_exists($this->files, 'createFolder'));
    }

    public function testDeleteMethodExists()
    {
        $this->assertTrue(method_exists($this->files, 'delete'));
    }

    public function testCopyMethodExists()
    {
        $this->assertTrue(method_exists($this->files, 'copy'));
    }

    public function testMoveMethodExists()
    {
        $this->assertTrue(method_exists($this->files, 'move'));
    }

    public function testSearchMethodExists()
    {
        $this->assertTrue(method_exists($this->files, 'search'));
    }

    public function testGetMetadataMethodExists()
    {
        $this->assertTrue(method_exists($this->files, 'getMetadata'));
    }
}
