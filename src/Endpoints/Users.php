<?php

namespace Tigusigalpa\Dropbox\Endpoints;

use Tigusigalpa\Dropbox\DropboxClient;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

/**
 * Users endpoint for Dropbox API
 *
 * Handles user account operations including getting account info,
 * space usage, and profile management.
 *
 * @link https://www.dropbox.com/developers/documentation/http/documentation#users
 */
class Users
{
    private DropboxClient $client;

    public function __construct(DropboxClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get information about the current user's account.
     *
     * @return array Current user's account information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#users-get_current_account
     */
    public function getCurrentAccount(): array
    {
        return $this->client->rpcRequest('/users/get_current_account');
    }

    /**
     * Get information about a user's account.
     *
     * @param string $accountId Account ID of the user
     * @return array User's account information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#users-get_account
     */
    public function getAccount(string $accountId): array
    {
        return $this->client->rpcRequest('/users/get_account', [
            'account_id' => $accountId,
        ]);
    }

    /**
     * Get information about multiple user accounts.
     *
     * @param array $accountIds Array of account IDs
     * @return array Array of user account information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#users-get_account_batch
     */
    public function getAccountBatch(array $accountIds): array
    {
        return $this->client->rpcRequest('/users/get_account_batch', [
            'account_ids' => $accountIds,
        ]);
    }

    /**
     * Get the space usage information for the current user's account.
     *
     * @return array Space usage information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#users-get_space_usage
     */
    public function getSpaceUsage(): array
    {
        return $this->client->rpcRequest('/users/get_space_usage');
    }

    /**
     * Get features available to the current user's account.
     *
     * @param array $features Array of feature names to query
     * @return array Features information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#users-features-get_values
     */
    public function getFeaturesValues(array $features): array
    {
        return $this->client->rpcRequest('/users/features/get_values', [
            'features' => array_map(fn($feature) => ['.tag' => $feature], $features),
        ]);
    }
}
