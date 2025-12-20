<?php

namespace Tigusigalpa\Dropbox\Endpoints;

use Tigusigalpa\Dropbox\DropboxClient;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

/**
 * Paper endpoint for Dropbox API
 *
 * Handles Dropbox Paper document operations.
 *
 * @link https://www.dropbox.com/developers/documentation/http/documentation#paper
 */
class Paper
{
    private DropboxClient $client;

    public function __construct(DropboxClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new Paper document.
     *
     * @param string $content Document content in HTML or Markdown
     * @param string $importFormat Import format ('html' or 'markdown')
     * @param string|null $parentFolderId Optional parent folder ID
     * @return array Created document information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#paper-docs-create
     */
    public function docsCreate(
        string $content,
        string $importFormat = 'html',
        ?string $parentFolderId = null
    ): array {
        $params = [
            'import_format' => ['.tag' => $importFormat],
        ];

        if ($parentFolderId) {
            $params['parent_folder_id'] = $parentFolderId;
        }

        return $this->client->contentUploadRequest('/paper/docs/create', $content, $params);
    }

    /**
     * Download a Paper document.
     *
     * @param string $docId Paper document ID
     * @param string $exportFormat Export format ('html' or 'markdown')
     * @return array Document content and metadata
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#paper-docs-download
     */
    public function docsDownload(string $docId, string $exportFormat = 'html'): array
    {
        return $this->client->contentDownloadRequest('/paper/docs/download', [
            'doc_id' => $docId,
            'export_format' => ['.tag' => $exportFormat],
        ]);
    }

    /**
     * Get metadata about a Paper document.
     *
     * @param string $docId Paper document ID
     * @return array Document metadata
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#paper-docs-get_metadata
     */
    public function docsGetMetadata(string $docId): array
    {
        return $this->client->rpcRequest('/paper/docs/get_metadata', [
            'doc_id' => $docId,
        ]);
    }

    /**
     * List Paper documents.
     *
     * @param string $filterBy Filter by ('docs_accessed' or 'docs_created')
     * @param string $sortBy Sort by ('accessed' or 'modified' or 'created')
     * @param string $sortOrder Sort order ('ascending' or 'descending')
     * @param int $limit Maximum number of results
     * @return array List of Paper documents
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#paper-docs-list
     */
    public function docsList(
        string $filterBy = 'docs_accessed',
        string $sortBy = 'accessed',
        string $sortOrder = 'descending',
        int $limit = 100
    ): array {
        return $this->client->rpcRequest('/paper/docs/list', [
            'filter_by' => ['.tag' => $filterBy],
            'sort_by' => ['.tag' => $sortBy],
            'sort_order' => ['.tag' => $sortOrder],
            'limit' => $limit,
        ]);
    }

    /**
     * Continue listing Paper documents.
     *
     * @param string $cursor Cursor from previous list call
     * @return array More Paper documents
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#paper-docs-list-continue
     */
    public function docsListContinue(string $cursor): array
    {
        return $this->client->rpcRequest('/paper/docs/list/continue', [
            'cursor' => $cursor,
        ]);
    }

    /**
     * Permanently delete a Paper document.
     *
     * @param string $docId Paper document ID
     * @return array Deletion result
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#paper-docs-permanently_delete
     */
    public function docsPermanentlyDelete(string $docId): array
    {
        return $this->client->rpcRequest('/paper/docs/permanently_delete', [
            'doc_id' => $docId,
        ]);
    }

    /**
     * Update a Paper document.
     *
     * @param string $docId Paper document ID
     * @param string $content New document content
     * @param string $importFormat Import format ('html' or 'markdown')
     * @param string $docUpdatePolicy Update policy ('append', 'prepend', 'overwrite_all')
     * @param int $revision Document revision
     * @return array Update result
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#paper-docs-update
     */
    public function docsUpdate(
        string $docId,
        string $content,
        string $importFormat = 'html',
        string $docUpdatePolicy = 'append',
        int $revision = 1
    ): array {
        return $this->client->contentUploadRequest('/paper/docs/update', $content, [
            'doc_id' => $docId,
            'import_format' => ['.tag' => $importFormat],
            'doc_update_policy' => ['.tag' => $docUpdatePolicy],
            'revision' => $revision,
        ]);
    }

    /**
     * Share a Paper document.
     *
     * @param string $docId Paper document ID
     * @param array $members Members to share with
     * @param string|null $customMessage Optional custom message
     * @param bool $quiet Don't send notifications
     * @return array Sharing result
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#paper-docs-sharing_policy-set
     */
    public function docsUsersAdd(
        string $docId,
        array $members,
        ?string $customMessage = null,
        bool $quiet = false
    ): array {
        $params = [
            'doc_id' => $docId,
            'members' => $members,
            'quiet' => $quiet,
        ];

        if ($customMessage) {
            $params['custom_message'] = $customMessage;
        }

        return $this->client->rpcRequest('/paper/docs/users/add', $params);
    }

    /**
     * List users with access to a Paper document.
     *
     * @param string $docId Paper document ID
     * @param int $limit Maximum number of results
     * @return array List of users
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#paper-docs-users-list
     */
    public function docsUsersList(string $docId, int $limit = 100): array
    {
        return $this->client->rpcRequest('/paper/docs/users/list', [
            'doc_id' => $docId,
            'limit' => $limit,
        ]);
    }

    /**
     * Continue listing users with access to a Paper document.
     *
     * @param string $docId Paper document ID
     * @param string $cursor Cursor from previous list call
     * @return array More users
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#paper-docs-users-list-continue
     */
    public function docsUsersListContinue(string $docId, string $cursor): array
    {
        return $this->client->rpcRequest('/paper/docs/users/list/continue', [
            'doc_id' => $docId,
            'cursor' => $cursor,
        ]);
    }

    /**
     * Remove users from a Paper document.
     *
     * @param string $docId Paper document ID
     * @param array $members Members to remove
     * @return array Removal result
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#paper-docs-users-remove
     */
    public function docsUsersRemove(string $docId, array $members): array
    {
        return $this->client->rpcRequest('/paper/docs/users/remove', [
            'doc_id' => $docId,
            'members' => $members,
        ]);
    }
}
