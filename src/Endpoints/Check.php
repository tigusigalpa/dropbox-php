<?php

namespace Tigusigalpa\Dropbox\Endpoints;

use Tigusigalpa\Dropbox\DropboxClient;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

/**
 * Check endpoint for Dropbox API
 *
 * Handles API connectivity and health checks.
 *
 * @link https://www.dropbox.com/developers/documentation/http/documentation#check
 */
class Check
{
    private DropboxClient $client;

    public function __construct(DropboxClient $client)
    {
        $this->client = $client;
    }

    /**
     * Check user's authentication and connectivity.
     *
     * @return array Check result
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#check-user
     */
    public function user(): array
    {
        return $this->client->rpcRequest('/check/user', [
            'query' => 'foo',
        ]);
    }

    /**
     * Check app's authentication and connectivity.
     *
     * @return array Check result
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#check-app
     */
    public function app(): array
    {
        return $this->client->rpcRequest('/check/app', [
            'query' => 'foo',
        ]);
    }
}
