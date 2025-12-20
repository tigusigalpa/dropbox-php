<?php

namespace Tigusigalpa\Dropbox\Tests;

use PHPUnit\Framework\TestCase;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

class ExceptionTest extends TestCase
{
    public function testDropboxExceptionInstantiation()
    {
        $exception = new DropboxException('Test error', 400, ['error' => 'test']);
        
        $this->assertInstanceOf(DropboxException::class, $exception);
        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
    }

    public function testGetResponse()
    {
        $response = ['error' => 'test_error', 'error_summary' => 'Test error summary'];
        $exception = new DropboxException('Test error', 400, $response);
        
        $this->assertEquals($response, $exception->getResponse());
    }

    public function testGetErrorSummary()
    {
        $response = ['error_summary' => 'path/not_found/...'];
        $exception = new DropboxException('Test error', 409, $response);
        
        $this->assertEquals('path/not_found/...', $exception->getErrorSummary());
    }

    public function testGetErrorTag()
    {
        $response = ['error' => ['.tag' => 'path_not_found']];
        $exception = new DropboxException('Test error', 409, $response);
        
        $this->assertEquals('path_not_found', $exception->getErrorTag());
    }

    public function testGetErrorSummaryReturnsNullWhenNotSet()
    {
        $exception = new DropboxException('Test error', 400, ['error' => 'test']);
        
        $this->assertNull($exception->getErrorSummary());
    }

    public function testGetErrorTagReturnsNullWhenNotSet()
    {
        $exception = new DropboxException('Test error', 400, ['error' => 'test']);
        
        $this->assertNull($exception->getErrorTag());
    }
}
