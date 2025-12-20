<?php

namespace Tigusigalpa\Dropbox\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Tigusigalpa\Dropbox\Endpoints\Files files()
 * @method static \Tigusigalpa\Dropbox\Endpoints\Sharing sharing()
 * @method static \Tigusigalpa\Dropbox\Endpoints\Users users()
 * @method static \Tigusigalpa\Dropbox\Endpoints\FileRequests fileRequests()
 * @method static \Tigusigalpa\Dropbox\Endpoints\Paper paper()
 * @method static \Tigusigalpa\Dropbox\Endpoints\Check check()
 * @method static string getAccessToken()
 * @method static void setAccessToken(string $accessToken)
 * @method static array rpcRequest(string $endpoint, array $params = [])
 * @method static array contentUploadRequest(string $endpoint, string $content, array $params = [])
 * @method static array contentDownloadRequest(string $endpoint, array $params = [])
 * @method static string getAuthorizationUrl(string $clientId, string $redirectUri, string $state = '', array $scope = [])
 * @method static array getAccessToken(string $code, string $clientId, string $clientSecret, string $redirectUri)
 * @method static array refreshAccessToken(string $refreshToken, string $clientId, string $clientSecret)
 *
 * @see \Tigusigalpa\Dropbox\DropboxClient
 */
class Dropbox extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'dropbox';
    }
}
