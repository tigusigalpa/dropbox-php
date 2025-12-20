<?php

namespace Tigusigalpa\Dropbox;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;
use Tigusigalpa\Dropbox\Endpoints\Files;
use Tigusigalpa\Dropbox\Endpoints\Sharing;
use Tigusigalpa\Dropbox\Endpoints\Users;
use Tigusigalpa\Dropbox\Endpoints\FileRequests;
use Tigusigalpa\Dropbox\Endpoints\Paper;
use Tigusigalpa\Dropbox\Endpoints\Check;

/**
 * Dropbox API Client
 *
 * A comprehensive PHP client for interacting with Dropbox API v2.
 * This class provides access to all major Dropbox API endpoints including
 * file operations, sharing, user management, and more.
 *
 * @package Tigusigalpa\Dropbox
 * @author  Igor Sazonov
 * @version 1.0.0
 * @link    https://www.dropbox.com/developers/documentation/http/documentation
 */
class DropboxClient
{
    private const API_BASE_URL = 'https://api.dropboxapi.com/2';
    private const API_CONTENT_URL = 'https://content.dropboxapi.com/2';
    private const OAUTH_AUTHORIZE_URL = 'https://www.dropbox.com/oauth2/authorize';
    private const OAUTH_TOKEN_URL = 'https://api.dropboxapi.com/oauth2/token';

    private GuzzleClient $client;
    private string $accessToken;

    public Files $files;
    public Sharing $sharing;
    public Users $users;
    public FileRequests $fileRequests;
    public Paper $paper;
    public Check $check;

    /**
     * DropboxClient constructor.
     *
     * @param string $accessToken OAuth access token for Dropbox API
     */
    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->client = new GuzzleClient([
            'verify' => false,
            'timeout' => 300,
        ]);

        $this->files = new Files($this);
        $this->sharing = new Sharing($this);
        $this->users = new Users($this);
        $this->fileRequests = new FileRequests($this);
        $this->paper = new Paper($this);
        $this->check = new Check($this);
    }

    /**
     * Generate OAuth authorization URL for Dropbox.
     *
     * @param string $clientId Client ID from Dropbox App Console
     * @param string $redirectUri Redirect URI configured in your app
     * @param string $state Optional state parameter for CSRF protection
     * @param array $scope Optional array of scopes to request
     * @return string Complete authorization URL
     */
    public static function getAuthorizationUrl(
        string $clientId,
        string $redirectUri,
        string $state = '',
        array $scope = []
    ): string {
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
        ];

        if ($state) {
            $params['state'] = $state;
        }

        if (!empty($scope)) {
            $params['scope'] = implode(' ', $scope);
        }

        return self::OAUTH_AUTHORIZE_URL . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token.
     *
     * @param string $code Authorization code from OAuth callback
     * @param string $clientId Client ID from Dropbox App Console
     * @param string $clientSecret Client Secret from Dropbox App Console
     * @param string $redirectUri Redirect URI used in authorization
     * @return array Token response with access_token, token_type, etc.
     * @throws DropboxException If token exchange fails
     */
    public static function getAccessToken(
        string $code,
        string $clientId,
        string $clientSecret,
        string $redirectUri
    ): array {
        $client = new GuzzleClient(['verify' => false]);

        try {
            $response = $client->post(self::OAUTH_TOKEN_URL, [
                'form_params' => [
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirectUri,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new DropboxException("Failed to get access token: " . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Refresh access token using refresh token.
     *
     * @param string $refreshToken Refresh token
     * @param string $clientId Client ID from Dropbox App Console
     * @param string $clientSecret Client Secret from Dropbox App Console
     * @return array Token response with new access_token
     * @throws DropboxException If token refresh fails
     */
    public static function refreshAccessToken(
        string $refreshToken,
        string $clientId,
        string $clientSecret
    ): array {
        $client = new GuzzleClient(['verify' => false]);

        try {
            $response = $client->post(self::OAUTH_TOKEN_URL, [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new DropboxException("Failed to refresh access token: " . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Make RPC-style API request to Dropbox.
     *
     * @param string $endpoint API endpoint path
     * @param array $params Request parameters
     * @return array API response data
     * @throws DropboxException If API request fails
     */
    public function rpcRequest(string $endpoint, array $params = []): array
    {
        return $this->request('POST', self::API_BASE_URL . $endpoint, [
            'json' => $params,
            'headers' => [
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Make content upload request to Dropbox.
     *
     * @param string $endpoint API endpoint path
     * @param string $content File content to upload
     * @param array $params Dropbox-API-Arg parameters
     * @return array API response data
     * @throws DropboxException If API request fails
     */
    public function contentUploadRequest(string $endpoint, string $content, array $params = []): array
    {
        return $this->request('POST', self::API_CONTENT_URL . $endpoint, [
            'body' => $content,
            'headers' => [
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/octet-stream',
                'Dropbox-API-Arg' => json_encode($params),
            ],
        ]);
    }

    /**
     * Make content download request to Dropbox.
     *
     * @param string $endpoint API endpoint path
     * @param array $params Dropbox-API-Arg parameters
     * @return array Response with 'content' and 'metadata' keys
     * @throws DropboxException If API request fails
     */
    public function contentDownloadRequest(string $endpoint, array $params = []): array
    {
        try {
            $response = $this->client->post(self::API_CONTENT_URL . $endpoint, [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                    'Dropbox-API-Arg' => json_encode($params),
                ],
            ]);

            $metadata = $response->getHeader('Dropbox-API-Result');
            $metadataArray = !empty($metadata) ? json_decode($metadata[0], true) : [];

            return [
                'content' => $response->getBody()->getContents(),
                'metadata' => $metadataArray,
            ];
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }

    /**
     * Make HTTP request to Dropbox API.
     *
     * @param string $method HTTP method
     * @param string $url Full URL
     * @param array $options Request options
     * @return array API response data
     * @throws DropboxException If API request fails
     */
    private function request(string $method, string $url, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $url, $options);
            $body = $response->getBody()->getContents();

            if (empty($body)) {
                return ['status' => $response->getStatusCode()];
            }

            return json_decode($body, true) ?? [];
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle Guzzle exceptions and convert to DropboxException.
     *
     * @param GuzzleException $e
     * @throws DropboxException
     */
    private function handleException(GuzzleException $e): void
    {
        $statusCode = $e->getCode();
        $response = null;

        if (method_exists($e, 'hasResponse') && $e->hasResponse()) {
            $body = $e->getResponse()->getBody()->getContents();
            $response = json_decode($body, true);
        }

        throw new DropboxException(
            $e->getMessage(),
            $statusCode,
            $response
        );
    }

    /**
     * Get the access token.
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Set a new access token.
     *
     * @param string $accessToken
     * @return void
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }
}
