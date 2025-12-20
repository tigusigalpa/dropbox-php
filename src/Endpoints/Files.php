<?php

namespace Tigusigalpa\Dropbox\Endpoints;

use Tigusigalpa\Dropbox\DropboxClient;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

/**
 * Files endpoint for Dropbox API
 *
 * Handles file and folder operations including upload, download,
 * move, copy, delete, search, and metadata operations.
 *
 * @link https://www.dropbox.com/developers/documentation/http/documentation#files
 */
class Files
{
    private DropboxClient $client;

    public function __construct(DropboxClient $client)
    {
        $this->client = $client;
    }

    /**
     * Copy a file or folder to a different location.
     *
     * @param string $fromPath Source path
     * @param string $toPath Destination path
     * @param bool $autorename If there's a conflict, autorename the file
     * @param bool $allowOwnershipTransfer Allow moving shared folders
     * @return array Metadata of the copied file/folder
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-copy_v2
     */
    public function copy(
        string $fromPath,
        string $toPath,
        bool $autorename = false,
        bool $allowOwnershipTransfer = false
    ): array {
        return $this->client->rpcRequest('/files/copy_v2', [
            'from_path' => $fromPath,
            'to_path' => $toPath,
            'autorename' => $autorename,
            'allow_ownership_transfer' => $allowOwnershipTransfer,
        ]);
    }

    /**
     * Copy a file or folder at a given path to a batch.
     *
     * @param array $entries Array of copy entries with from_path and to_path
     * @param bool $autorename If there's a conflict, autorename the file
     * @param bool $allowOwnershipTransfer Allow moving shared folders
     * @return array Async job ID or complete status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-copy_batch_v2
     */
    public function copyBatch(
        array $entries,
        bool $autorename = false,
        bool $allowOwnershipTransfer = false
    ): array {
        return $this->client->rpcRequest('/files/copy_batch_v2', [
            'entries' => $entries,
            'autorename' => $autorename,
            'allow_ownership_transfer' => $allowOwnershipTransfer,
        ]);
    }

    /**
     * Check the status of a copy_batch job.
     *
     * @param string $asyncJobId Async job ID from copy_batch
     * @return array Job status and results
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-copy_batch-check_v2
     */
    public function copyBatchCheck(string $asyncJobId): array
    {
        return $this->client->rpcRequest('/files/copy_batch/check_v2', [
            'async_job_id' => $asyncJobId,
        ]);
    }

    /**
     * Create a folder at a given path.
     *
     * @param string $path Path in the user's Dropbox to create
     * @param bool $autorename If there's a conflict, autorename the folder
     * @return array Metadata of the created folder
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-create_folder_v2
     */
    public function createFolder(string $path, bool $autorename = false): array
    {
        return $this->client->rpcRequest('/files/create_folder_v2', [
            'path' => $path,
            'autorename' => $autorename,
        ]);
    }

    /**
     * Create multiple folders at once.
     *
     * @param array $paths Array of folder paths to create
     * @param bool $autorename If there's a conflict, autorename the folders
     * @param bool $forceAsync Force async processing
     * @return array Results of folder creation
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-create_folder_batch
     */
    public function createFolderBatch(array $paths, bool $autorename = false, bool $forceAsync = false): array
    {
        return $this->client->rpcRequest('/files/create_folder_batch', [
            'paths' => $paths,
            'autorename' => $autorename,
            'force_async' => $forceAsync,
        ]);
    }

    /**
     * Delete a file or folder at a given path.
     *
     * @param string $path Path to delete
     * @return array Metadata of the deleted file/folder
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-delete_v2
     */
    public function delete(string $path): array
    {
        return $this->client->rpcRequest('/files/delete_v2', [
            'path' => $path,
        ]);
    }

    /**
     * Delete multiple files/folders at once.
     *
     * @param array $entries Array of paths to delete
     * @return array Async job ID or complete status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-delete_batch
     */
    public function deleteBatch(array $entries): array
    {
        return $this->client->rpcRequest('/files/delete_batch', [
            'entries' => array_map(fn($path) => ['path' => $path], $entries),
        ]);
    }

    /**
     * Download a file from a user's Dropbox.
     *
     * @param string $path Path to the file to download
     * @param string|null $rev Optional revision to download
     * @return array Array with 'content' and 'metadata' keys
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-download
     */
    public function download(string $path, ?string $rev = null): array
    {
        $params = ['path' => $path];
        if ($rev) {
            $params['rev'] = $rev;
        }

        return $this->client->contentDownloadRequest('/files/download', $params);
    }

    /**
     * Download a folder as a zip file.
     *
     * @param string $path Path to the folder to download
     * @return array Array with 'content' and 'metadata' keys
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-download_zip
     */
    public function downloadZip(string $path): array
    {
        return $this->client->contentDownloadRequest('/files/download_zip', [
            'path' => $path,
        ]);
    }

    /**
     * Export a file from Dropbox (e.g., Paper docs, Google Docs).
     *
     * @param string $path Path to the file to export
     * @param string|null $exportFormat Export format (e.g., 'html', 'markdown', 'pdf')
     * @return array Array with 'content' and 'metadata' keys
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-export
     */
    public function export(string $path, ?string $exportFormat = null): array
    {
        $params = ['path' => $path];
        if ($exportFormat) {
            $params['export_format'] = $exportFormat;
        }

        return $this->client->contentDownloadRequest('/files/export', $params);
    }

    /**
     * Get a copy reference to a file or folder.
     *
     * @param string $path Path to the file or folder
     * @return array Copy reference information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-copy_reference-get
     */
    public function getCopyReference(string $path): array
    {
        return $this->client->rpcRequest('/files/copy_reference/get', [
            'path' => $path,
        ]);
    }

    /**
     * Save a copy reference to the user's Dropbox.
     *
     * @param string $copyReference Copy reference returned by getCopyReference
     * @param string $path Path where to save the file/folder
     * @return array Metadata of the saved file/folder
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-copy_reference-save
     */
    public function saveCopyReference(string $copyReference, string $path): array
    {
        return $this->client->rpcRequest('/files/copy_reference/save', [
            'copy_reference' => $copyReference,
            'path' => $path,
        ]);
    }

    /**
     * Get metadata for a file or folder.
     *
     * @param string $path Path to the file or folder
     * @param bool $includeMediaInfo If true, include media info for photos and videos
     * @param bool $includeDeleted If true, include deleted files
     * @param bool $includeHasExplicitSharedMembers Include info about shared members
     * @return array File or folder metadata
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-get_metadata
     */
    public function getMetadata(
        string $path,
        bool $includeMediaInfo = false,
        bool $includeDeleted = false,
        bool $includeHasExplicitSharedMembers = false
    ): array {
        return $this->client->rpcRequest('/files/get_metadata', [
            'path' => $path,
            'include_media_info' => $includeMediaInfo,
            'include_deleted' => $includeDeleted,
            'include_has_explicit_shared_members' => $includeHasExplicitSharedMembers,
        ]);
    }

    /**
     * Get a preview for a file.
     *
     * @param string $path Path to the file
     * @param string|null $rev Optional revision
     * @return array Array with 'content' and 'metadata' keys
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-get_preview
     */
    public function getPreview(string $path, ?string $rev = null): array
    {
        $params = ['path' => $path];
        if ($rev) {
            $params['rev'] = $rev;
        }

        return $this->client->contentDownloadRequest('/files/get_preview', $params);
    }

    /**
     * Get a temporary link to stream content of a file.
     *
     * @param string $path Path to the file
     * @return array Temporary link information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-get_temporary_link
     */
    public function getTemporaryLink(string $path): array
    {
        return $this->client->rpcRequest('/files/get_temporary_link', [
            'path' => $path,
        ]);
    }

    /**
     * Get a temporary upload link.
     *
     * @param string $commitInfo Commit info for the upload
     * @param int $duration How long the link is valid (in seconds, max 14400)
     * @return array Temporary upload link
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-get_temporary_upload_link
     */
    public function getTemporaryUploadLink(array $commitInfo, int $duration = 14400): array
    {
        return $this->client->rpcRequest('/files/get_temporary_upload_link', [
            'commit_info' => $commitInfo,
            'duration' => $duration,
        ]);
    }

    /**
     * Get a thumbnail for an image.
     *
     * @param string $path Path to the image
     * @param string $format Thumbnail format ('jpeg' or 'png')
     * @param string $size Thumbnail size ('w32h32', 'w64h64', 'w128h128', 'w256h256', 'w480h320', 'w640h480', 'w960h640', 'w1024h768', 'w2048h1536')
     * @param string $mode Thumbnail mode ('strict', 'bestfit', 'fitone_bestfit')
     * @return array Array with 'content' and 'metadata' keys
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-get_thumbnail_v2
     */
    public function getThumbnail(
        string $path,
        string $format = 'jpeg',
        string $size = 'w64h64',
        string $mode = 'strict'
    ): array {
        return $this->client->contentDownloadRequest('/files/get_thumbnail_v2', [
            'resource' => ['.tag' => 'path', 'path' => $path],
            'format' => $format,
            'size' => $size,
            'mode' => $mode,
        ]);
    }

    /**
     * Get thumbnails for multiple files.
     *
     * @param array $entries Array of thumbnail entries
     * @return array Thumbnails data
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-get_thumbnail_batch
     */
    public function getThumbnailBatch(array $entries): array
    {
        return $this->client->rpcRequest('/files/get_thumbnail_batch', [
            'entries' => $entries,
        ]);
    }

    /**
     * List contents of a folder.
     *
     * @param string $path Path to the folder
     * @param bool $recursive If true, list all contents recursively
     * @param bool $includeMediaInfo Include media info for photos and videos
     * @param bool $includeDeleted Include deleted files
     * @param bool $includeHasExplicitSharedMembers Include shared member info
     * @param bool $includeMountedFolders Include mounted folders
     * @param int|null $limit Maximum number of results (max 2000)
     * @return array Folder contents and cursor for pagination
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-list_folder
     */
    public function listFolder(
        string $path = '',
        bool $recursive = false,
        bool $includeMediaInfo = false,
        bool $includeDeleted = false,
        bool $includeHasExplicitSharedMembers = false,
        bool $includeMountedFolders = true,
        ?int $limit = null
    ): array {
        $params = [
            'path' => $path,
            'recursive' => $recursive,
            'include_media_info' => $includeMediaInfo,
            'include_deleted' => $includeDeleted,
            'include_has_explicit_shared_members' => $includeHasExplicitSharedMembers,
            'include_mounted_folders' => $includeMountedFolders,
        ];

        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        return $this->client->rpcRequest('/files/list_folder', $params);
    }

    /**
     * Continue listing folder contents using a cursor.
     *
     * @param string $cursor Cursor from previous list_folder call
     * @return array More folder contents and new cursor
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-list_folder-continue
     */
    public function listFolderContinue(string $cursor): array
    {
        return $this->client->rpcRequest('/files/list_folder/continue', [
            'cursor' => $cursor,
        ]);
    }

    /**
     * Get latest cursor for a folder without listing contents.
     *
     * @param string $path Path to the folder
     * @param bool $recursive If true, get cursor for recursive listing
     * @return array Cursor information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-list_folder-get_latest_cursor
     */
    public function listFolderGetLatestCursor(string $path = '', bool $recursive = false): array
    {
        return $this->client->rpcRequest('/files/list_folder/get_latest_cursor', [
            'path' => $path,
            'recursive' => $recursive,
        ]);
    }

    /**
     * Get changes to a folder using a cursor.
     *
     * @param string $cursor Cursor from list_folder or list_folder/get_latest_cursor
     * @return array Changes and new cursor
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-list_folder-longpoll
     */
    public function listFolderLongpoll(string $cursor, int $timeout = 30): array
    {
        return $this->client->rpcRequest('/files/list_folder/longpoll', [
            'cursor' => $cursor,
            'timeout' => $timeout,
        ]);
    }

    /**
     * List revisions of a file.
     *
     * @param string $path Path to the file
     * @param string $mode Mode for listing revisions ('path' or 'id')
     * @param int $limit Maximum number of revisions to return (max 100)
     * @return array File revisions
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-list_revisions
     */
    public function listRevisions(string $path, string $mode = 'path', int $limit = 10): array
    {
        return $this->client->rpcRequest('/files/list_revisions', [
            'path' => $path,
            'mode' => $mode,
            'limit' => $limit,
        ]);
    }

    /**
     * Move a file or folder to a different location.
     *
     * @param string $fromPath Source path
     * @param string $toPath Destination path
     * @param bool $autorename If there's a conflict, autorename the file
     * @param bool $allowSharedFolder Allow moving shared folders
     * @param bool $allowOwnershipTransfer Allow ownership transfer
     * @return array Metadata of the moved file/folder
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-move_v2
     */
    public function move(
        string $fromPath,
        string $toPath,
        bool $autorename = false,
        bool $allowSharedFolder = false,
        bool $allowOwnershipTransfer = false
    ): array {
        return $this->client->rpcRequest('/files/move_v2', [
            'from_path' => $fromPath,
            'to_path' => $toPath,
            'autorename' => $autorename,
            'allow_shared_folder' => $allowSharedFolder,
            'allow_ownership_transfer' => $allowOwnershipTransfer,
        ]);
    }

    /**
     * Move multiple files or folders at once.
     *
     * @param array $entries Array of move entries with from_path and to_path
     * @param bool $autorename If there's a conflict, autorename files
     * @param bool $allowOwnershipTransfer Allow ownership transfer
     * @return array Async job ID or complete status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-move_batch_v2
     */
    public function moveBatch(
        array $entries,
        bool $autorename = false,
        bool $allowOwnershipTransfer = false
    ): array {
        return $this->client->rpcRequest('/files/move_batch_v2', [
            'entries' => $entries,
            'autorename' => $autorename,
            'allow_ownership_transfer' => $allowOwnershipTransfer,
        ]);
    }

    /**
     * Permanently delete a file or folder.
     *
     * @param string $path Path to permanently delete
     * @return array Response status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-permanently_delete
     */
    public function permanentlyDelete(string $path): array
    {
        return $this->client->rpcRequest('/files/permanently_delete', [
            'path' => $path,
        ]);
    }

    /**
     * Restore a file to a specific revision.
     *
     * @param string $path Path to the file
     * @param string $rev Revision to restore
     * @return array Metadata of the restored file
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-restore
     */
    public function restore(string $path, string $rev): array
    {
        return $this->client->rpcRequest('/files/restore', [
            'path' => $path,
            'rev' => $rev,
        ]);
    }

    /**
     * Save a file from a URL to Dropbox.
     *
     * @param string $path Path where to save the file
     * @param string $url URL of the file to save
     * @return array Async job ID
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-save_url
     */
    public function saveUrl(string $path, string $url): array
    {
        return $this->client->rpcRequest('/files/save_url', [
            'path' => $path,
            'url' => $url,
        ]);
    }

    /**
     * Check the status of a save_url job.
     *
     * @param string $asyncJobId Async job ID from save_url
     * @return array Job status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-save_url-check_job_status
     */
    public function saveUrlCheckJobStatus(string $asyncJobId): array
    {
        return $this->client->rpcRequest('/files/save_url/check_job_status', [
            'async_job_id' => $asyncJobId,
        ]);
    }

    /**
     * Search for files and folders.
     *
     * @param string $query Search query
     * @param string $path Path to search in (empty for entire Dropbox)
     * @param int $maxResults Maximum results to return (max 1000)
     * @param string $orderBy Order results by ('relevance' or 'last_modified_time')
     * @param string $fileStatus File status filter ('active', 'deleted', or 'both')
     * @param string|null $filenameOnly Search in filename only
     * @param array $fileExtensions Filter by file extensions
     * @param array $fileCategories Filter by file categories
     * @return array Search results
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-search_v2
     */
    public function search(
        string $query,
        string $path = '',
        int $maxResults = 100,
        string $orderBy = 'relevance',
        string $fileStatus = 'active',
        ?string $filenameOnly = null,
        array $fileExtensions = [],
        array $fileCategories = []
    ): array {
        $options = [];
        
        if ($path) {
            $options['path'] = $path;
        }
        
        if ($maxResults) {
            $options['max_results'] = $maxResults;
        }
        
        if ($orderBy) {
            $options['order_by'] = ['.tag' => $orderBy];
        }
        
        if ($fileStatus) {
            $options['file_status'] = ['.tag' => $fileStatus];
        }
        
        if ($filenameOnly) {
            $options['filename_only'] = $filenameOnly;
        }
        
        if (!empty($fileExtensions)) {
            $options['file_extensions'] = $fileExtensions;
        }
        
        if (!empty($fileCategories)) {
            $options['file_categories'] = array_map(fn($cat) => ['.tag' => $cat], $fileCategories);
        }

        return $this->client->rpcRequest('/files/search_v2', [
            'query' => $query,
            'options' => $options,
        ]);
    }

    /**
     * Continue a search using a cursor.
     *
     * @param string $cursor Cursor from previous search
     * @return array More search results
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-search-continue_v2
     */
    public function searchContinue(string $cursor): array
    {
        return $this->client->rpcRequest('/files/search/continue_v2', [
            'cursor' => $cursor,
        ]);
    }

    /**
     * Upload a file to Dropbox.
     *
     * @param string $path Path where to upload the file
     * @param string $content File content
     * @param string $mode Write mode ('add', 'overwrite', or 'update')
     * @param bool $autorename If there's a conflict, autorename the file
     * @param bool $mute Don't notify users about this change
     * @param bool $strictConflict Be strict about conflicts
     * @return array Metadata of the uploaded file
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload
     */
    public function upload(
        string $path,
        string $content,
        string $mode = 'add',
        bool $autorename = false,
        bool $mute = false,
        bool $strictConflict = false
    ): array {
        return $this->client->contentUploadRequest('/files/upload', $content, [
            'path' => $path,
            'mode' => $mode,
            'autorename' => $autorename,
            'mute' => $mute,
            'strict_conflict' => $strictConflict,
        ]);
    }

    /**
     * Start an upload session for large files.
     *
     * @param string $content First chunk of file content
     * @param bool $close Close the session after this chunk
     * @return array Session ID and offset
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-start
     */
    public function uploadSessionStart(string $content, bool $close = false): array
    {
        return $this->client->contentUploadRequest('/files/upload_session/start', $content, [
            'close' => $close,
        ]);
    }

    /**
     * Append data to an upload session.
     *
     * @param string $sessionId Upload session ID
     * @param int $offset Offset in bytes
     * @param string $content Content chunk to append
     * @param bool $close Close the session after this chunk
     * @return array Response status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-append_v2
     */
    public function uploadSessionAppend(
        string $sessionId,
        int $offset,
        string $content,
        bool $close = false
    ): array {
        return $this->client->contentUploadRequest('/files/upload_session/append_v2', $content, [
            'cursor' => [
                'session_id' => $sessionId,
                'offset' => $offset,
            ],
            'close' => $close,
        ]);
    }

    /**
     * Finish an upload session.
     *
     * @param string $sessionId Upload session ID
     * @param int $offset Offset in bytes
     * @param string $content Last content chunk
     * @param array $commit Commit info (path, mode, etc.)
     * @return array Metadata of the uploaded file
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-finish
     */
    public function uploadSessionFinish(
        string $sessionId,
        int $offset,
        string $content,
        array $commit
    ): array {
        return $this->client->contentUploadRequest('/files/upload_session/finish', $content, [
            'cursor' => [
                'session_id' => $sessionId,
                'offset' => $offset,
            ],
            'commit' => $commit,
        ]);
    }

    /**
     * Finish multiple upload sessions in a batch.
     *
     * @param array $entries Array of upload session finish entries
     * @return array Async job ID or complete status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-finish_batch
     */
    public function uploadSessionFinishBatch(array $entries): array
    {
        return $this->client->rpcRequest('/files/upload_session/finish_batch', [
            'entries' => $entries,
        ]);
    }

    /**
     * Check the status of an upload_session/finish_batch job.
     *
     * @param string $asyncJobId Async job ID
     * @return array Job status and results
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-finish_batch-check
     */
    public function uploadSessionFinishBatchCheck(string $asyncJobId): array
    {
        return $this->client->rpcRequest('/files/upload_session/finish_batch/check', [
            'async_job_id' => $asyncJobId,
        ]);
    }
}
