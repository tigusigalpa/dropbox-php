<?php

namespace Tigusigalpa\Dropbox\Endpoints;

use Tigusigalpa\Dropbox\DropboxClient;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

/**
 * File Requests endpoint for Dropbox API
 *
 * Handles file request operations for collecting files from others.
 *
 * @link https://www.dropbox.com/developers/documentation/http/documentation#file_requests
 */
class FileRequests
{
    private DropboxClient $client;

    public function __construct(DropboxClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a file request.
     *
     * @param string $title Title of the file request
     * @param string $destination Path where uploaded files will be stored
     * @param string|null $deadline Optional deadline for the file request
     * @param bool $open Whether the file request is open
     * @param string|null $description Optional description
     * @return array Created file request information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#file_requests-create
     */
    public function create(
        string $title,
        string $destination,
        ?string $deadline = null,
        bool $open = true,
        ?string $description = null
    ): array {
        $params = [
            'title' => $title,
            'destination' => $destination,
            'open' => $open,
        ];

        if ($deadline) {
            $params['deadline'] = ['deadline' => $deadline];
        }

        if ($description) {
            $params['description'] = $description;
        }

        return $this->client->rpcRequest('/file_requests/create', $params);
    }

    /**
     * Get information about a file request.
     *
     * @param string $id File request ID
     * @return array File request information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#file_requests-get
     */
    public function get(string $id): array
    {
        return $this->client->rpcRequest('/file_requests/get', [
            'id' => $id,
        ]);
    }

    /**
     * List all file requests owned by the user.
     *
     * @param int $limit Maximum number of file requests to return
     * @return array List of file requests
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#file_requests-list_v2
     */
    public function list(int $limit = 1000): array
    {
        return $this->client->rpcRequest('/file_requests/list_v2', [
            'limit' => $limit,
        ]);
    }

    /**
     * Continue listing file requests.
     *
     * @param string $cursor Cursor from previous list call
     * @return array More file requests
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#file_requests-list-continue
     */
    public function listContinue(string $cursor): array
    {
        return $this->client->rpcRequest('/file_requests/list/continue', [
            'cursor' => $cursor,
        ]);
    }

    /**
     * Update a file request.
     *
     * @param string $id File request ID
     * @param array $updates Updates to apply
     * @return array Updated file request information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#file_requests-update
     */
    public function update(string $id, array $updates): array
    {
        return $this->client->rpcRequest('/file_requests/update', array_merge(
            ['id' => $id],
            $updates
        ));
    }

    /**
     * Delete a file request.
     *
     * @param array $ids Array of file request IDs to delete
     * @return array Deletion results
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#file_requests-delete
     */
    public function delete(array $ids): array
    {
        return $this->client->rpcRequest('/file_requests/delete', [
            'ids' => $ids,
        ]);
    }

    /**
     * Delete all closed file requests owned by the user.
     *
     * @return array Deletion result
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#file_requests-delete_all_closed
     */
    public function deleteAllClosed(): array
    {
        return $this->client->rpcRequest('/file_requests/delete_all_closed');
    }

    /**
     * Count the number of file requests owned by the user.
     *
     * @return array File request count
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#file_requests-count
     */
    public function count(): array
    {
        return $this->client->rpcRequest('/file_requests/count');
    }
}
