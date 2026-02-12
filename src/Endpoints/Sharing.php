<?php

namespace Tigusigalpa\Dropbox\Endpoints;

use Tigusigalpa\Dropbox\DropboxClient;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

/**
 * Sharing endpoint for Dropbox API
 *
 * Handles sharing operations including shared folders, shared links,
 * file requests, and collaboration features.
 *
 * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing
 */
class Sharing
{
    private DropboxClient $client;

    public function __construct(DropboxClient $client)
    {
        $this->client = $client;
    }

    /**
     * Add folder members to a shared folder.
     *
     * @param string $sharedFolderId Shared folder ID
     * @param array $members Array of members to add
     * @param bool $quiet Don't send notifications
     * @param string|null $customMessage Custom message for invitation
     * @return array Response status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-add_folder_member
     */
    public function addFolderMember(
        string $sharedFolderId,
        array $members,
        bool $quiet = false,
        ?string $customMessage = null
    ): array {
        $params = [
            'shared_folder_id' => $sharedFolderId,
            'members' => $members,
            'quiet' => $quiet,
        ];

        if ($customMessage) {
            $params['custom_message'] = $customMessage;
        }

        return $this->client->rpcRequest('/sharing/add_folder_member', $params);
    }

    /**
     * Add file members to a shared file.
     *
     * @param string $file File path or ID
     * @param array $members Array of members to add
     * @param string|null $customMessage Custom message for invitation
     * @param bool $quiet Don't send notifications
     * @param string $accessLevel Access level ('viewer' or 'editor')
     * @param bool $addMessageAsComment Add message as a comment
     * @return array Added members information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-add_file_member
     */
    public function addFileMember(
        string $file,
        array $members,
        ?string $customMessage = null,
        bool $quiet = false,
        string $accessLevel = 'viewer',
        bool $addMessageAsComment = false
    ): array {
        $params = [
            'file' => $file,
            'members' => $members,
            'quiet' => $quiet,
            'access_level' => ['.tag' => $accessLevel],
            'add_message_as_comment' => $addMessageAsComment,
        ];

        if ($customMessage) {
            $params['custom_message'] = $customMessage;
        }

        return $this->client->rpcRequest('/sharing/add_file_member', $params);
    }

    /**
     * Check job status for async sharing operations.
     *
     * @param string $asyncJobId Async job ID
     * @return array Job status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-check_job_status
     */
    public function checkJobStatus(string $asyncJobId): array
    {
        return $this->client->rpcRequest('/sharing/check_job_status', [
            'async_job_id' => $asyncJobId,
        ]);
    }

    /**
     * Check share job status.
     *
     * @param string $asyncJobId Async job ID
     * @return array Job status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-check_share_job_status
     */
    public function checkShareJobStatus(string $asyncJobId): array
    {
        return $this->client->rpcRequest('/sharing/check_share_job_status', [
            'async_job_id' => $asyncJobId,
        ]);
    }

    /**
     * Create a shared link with settings.
     *
     * @param string $path Path to the file or folder
     * @param array $settings Link settings
     * @return array Shared link information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-create_shared_link_with_settings
     */
    public function createSharedLinkWithSettings(string $path, array $settings = []): array
    {
        return $this->client->rpcRequest('/sharing/create_shared_link_with_settings', [
            'path' => $path,
            'settings' => $settings,
        ]);
    }

    /**
     * Get file metadata from a shared link.
     *
     * @param string $url Shared link URL
     * @param string|null $path Path within the shared link
     * @param string|null $linkPassword Password for the shared link
     * @return array File metadata
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-get_file_metadata
     */
    public function getFileMetadata(string $url, ?string $path = null, ?string $linkPassword = null): array
    {
        $params = ['url' => $url];

        if ($path) {
            $params['path'] = $path;
        }

        if ($linkPassword) {
            $params['link_password'] = $linkPassword;
        }

        return $this->client->rpcRequest('/sharing/get_file_metadata', $params);
    }

    /**
     * Get metadata for a batch of shared links.
     *
     * @param array $urls Array of shared link URLs
     * @return array Metadata for each link
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-get_file_metadata-batch
     */
    public function getFileMetadataBatch(array $urls): array
    {
        return $this->client->rpcRequest('/sharing/get_file_metadata/batch', [
            'urls' => $urls,
        ]);
    }

    /**
     * Get folder metadata from a shared link.
     *
     * @param string $url Shared link URL
     * @param string|null $path Path within the shared link
     * @param string|null $linkPassword Password for the shared link
     * @return array Folder metadata
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-get_folder_metadata
     */
    public function getFolderMetadata(string $url, ?string $path = null, ?string $linkPassword = null): array
    {
        $params = ['url' => $url];

        if ($path) {
            $params['path'] = $path;
        }

        if ($linkPassword) {
            $params['link_password'] = $linkPassword;
        }

        return $this->client->rpcRequest('/sharing/get_folder_metadata', $params);
    }

    /**
     * Get information about a shared folder.
     *
     * @param string $sharedFolderId Shared folder ID
     * @param array $actions List of actions to check
     * @return array Shared folder metadata
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-get_shared_link_metadata
     */
    public function getSharedLinkMetadata(string $url, ?string $path = null, ?string $linkPassword = null): array
    {
        $params = ['url' => $url];

        if ($path) {
            $params['path'] = $path;
        }

        if ($linkPassword) {
            $params['link_password'] = $linkPassword;
        }

        return $this->client->rpcRequest('/sharing/get_shared_link_metadata', $params);
    }

    /**
     * Get information about a shared folder by its ID.
     *
     * @param string $sharedFolderId Shared folder ID
     * @param array $actions Actions to check permissions for
     * @return array Shared folder information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-get_folder_metadata
     */
    public function getSharedFolderMetadata(string $sharedFolderId, array $actions = []): array
    {
        $params = ['shared_folder_id' => $sharedFolderId];

        if (!empty($actions)) {
            $params['actions'] = $actions;
        }

        return $this->client->rpcRequest('/sharing/get_folder_metadata', $params);
    }

    /**
     * List shared links.
     *
     * @param string|null $path Filter by path
     * @param string|null $cursor Cursor for pagination
     * @param bool $directOnly Only direct links
     * @return array List of shared links
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_shared_links
     */
    public function listSharedLinks(?string $path = null, ?string $cursor = null, bool $directOnly = false): array
    {
        $params = [];

        if ($path) {
            $params['path'] = $path;
        }

        if ($cursor) {
            $params['cursor'] = $cursor;
        }

        if ($directOnly) {
            $params['direct_only'] = true;
        }

        return $this->client->rpcRequest('/sharing/list_shared_links', $params);
    }

    /**
     * List folders shared with the user.
     *
     * @param int $limit Maximum number of results
     * @param array $actions Actions to check permissions for
     * @return array List of shared folders
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_folders
     */
    public function listFolders(int $limit = 100, array $actions = []): array
    {
        $params = ['limit' => $limit];

        if (!empty($actions)) {
            $params['actions'] = $actions;
        }

        return $this->client->rpcRequest('/sharing/list_folders', $params);
    }

    /**
     * Continue listing shared folders.
     *
     * @param string $cursor Cursor from previous list_folders call
     * @return array More shared folders
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_folders-continue
     */
    public function listFoldersContinue(string $cursor): array
    {
        return $this->client->rpcRequest('/sharing/list_folders/continue', [
            'cursor' => $cursor,
        ]);
    }

    /**
     * List members of a shared folder.
     *
     * @param string $sharedFolderId Shared folder ID
     * @param array $actions Actions to check permissions for
     * @param int $limit Maximum number of results
     * @return array List of folder members
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_folder_members
     */
    public function listFolderMembers(string $sharedFolderId, array $actions = [], int $limit = 100): array
    {
        $params = [
            'shared_folder_id' => $sharedFolderId,
            'limit' => $limit,
        ];

        if (!empty($actions)) {
            $params['actions'] = $actions;
        }

        return $this->client->rpcRequest('/sharing/list_folder_members', $params);
    }

    /**
     * Continue listing folder members.
     *
     * @param string $cursor Cursor from previous list_folder_members call
     * @return array More folder members
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_folder_members-continue
     */
    public function listFolderMembersContinue(string $cursor): array
    {
        return $this->client->rpcRequest('/sharing/list_folder_members/continue', [
            'cursor' => $cursor,
        ]);
    }

    /**
     * List files that are members of shared folders.
     *
     * @param int $limit Maximum number of results
     * @param array $actions Actions to check permissions for
     * @return array List of file members
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_file_members
     */
    public function listFileMembersBatch(array $files, int $limit = 10): array
    {
        return $this->client->rpcRequest('/sharing/list_file_members/batch', [
            'files' => $files,
            'limit' => $limit,
        ]);
    }

    /**
     * List members of a file.
     *
     * @param string $file File path or ID
     * @param array $actions Actions to check permissions for
     * @param bool $includeInherited Include inherited members
     * @param int $limit Maximum number of results
     * @return array List of file members
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_file_members
     */
    public function listFileMembers(
        string $file,
        array $actions = [],
        bool $includeInherited = true,
        int $limit = 100
    ): array {
        $params = [
            'file' => $file,
            'include_inherited' => $includeInherited,
            'limit' => $limit,
        ];

        if (!empty($actions)) {
            $params['actions'] = $actions;
        }

        return $this->client->rpcRequest('/sharing/list_file_members', $params);
    }

    /**
     * Continue listing file members.
     *
     * @param string $cursor Cursor from previous list_file_members call
     * @return array More file members
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_file_members-continue
     */
    public function listFileMembersContinue(string $cursor): array
    {
        return $this->client->rpcRequest('/sharing/list_file_members/continue', [
            'cursor' => $cursor,
        ]);
    }

    /**
     * List received files.
     *
     * @param int $limit Maximum number of results
     * @param array $actions Actions to check permissions for
     * @return array List of received files
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_received_files
     */
    public function listReceivedFiles(int $limit = 100, array $actions = []): array
    {
        $params = ['limit' => $limit];

        if (!empty($actions)) {
            $params['actions'] = $actions;
        }

        return $this->client->rpcRequest('/sharing/list_received_files', $params);
    }

    /**
     * Continue listing received files.
     *
     * @param string $cursor Cursor from previous list_received_files call
     * @return array More received files
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-list_received_files-continue
     */
    public function listReceivedFilesContinue(string $cursor): array
    {
        return $this->client->rpcRequest('/sharing/list_received_files/continue', [
            'cursor' => $cursor,
        ]);
    }

    /**
     * Modify shared link settings.
     *
     * @param string $url Shared link URL
     * @param array $settings New settings
     * @param bool $removeExpiration Remove expiration date
     * @return array Updated shared link
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-modify_shared_link_settings
     */
    public function modifySharedLinkSettings(
        string $url,
        array $settings = [],
        bool $removeExpiration = false
    ): array {
        $params = [
            'url' => $url,
            'settings' => $settings,
            'remove_expiration' => $removeExpiration,
        ];

        return $this->client->rpcRequest('/sharing/modify_shared_link_settings', $params);
    }

    /**
     * Mount a shared folder.
     *
     * @param string $sharedFolderId Shared folder ID
     * @return array Mounted folder metadata
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-mount_folder
     */
    public function mountFolder(string $sharedFolderId): array
    {
        return $this->client->rpcRequest('/sharing/mount_folder', [
            'shared_folder_id' => $sharedFolderId,
        ]);
    }

    /**
     * Relinquish folder membership.
     *
     * @param string $sharedFolderId Shared folder ID
     * @param bool $leaveACopy Leave a copy of the folder
     * @return array Response status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-relinquish_folder_membership
     */
    public function relinquishFolderMembership(string $sharedFolderId, bool $leaveACopy = false): array
    {
        return $this->client->rpcRequest('/sharing/relinquish_folder_membership', [
            'shared_folder_id' => $sharedFolderId,
            'leave_a_copy' => $leaveACopy,
        ]);
    }

    /**
     * Relinquish file membership.
     *
     * @param string $file File path or ID
     * @return array Response status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-relinquish_file_membership
     */
    public function relinquishFileMembership(string $file): array
    {
        return $this->client->rpcRequest('/sharing/relinquish_file_membership', [
            'file' => $file,
        ]);
    }

    /**
     * Remove a folder member.
     *
     * @param string $sharedFolderId Shared folder ID
     * @param array $member Member to remove
     * @param bool $leaveACopy Leave a copy for the removed member
     * @return array Async job ID
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-remove_folder_member
     */
    public function removeFolderMember(string $sharedFolderId, array $member, bool $leaveACopy = false): array
    {
        return $this->client->rpcRequest('/sharing/remove_folder_member', [
            'shared_folder_id' => $sharedFolderId,
            'member' => $member,
            'leave_a_copy' => $leaveACopy,
        ]);
    }

    /**
     * Remove a file member.
     *
     * @param string $file File path or ID
     * @param array $member Member to remove
     * @return array Response status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-remove_file_member_2
     */
    public function removeFileMember(string $file, array $member): array
    {
        return $this->client->rpcRequest('/sharing/remove_file_member_2', [
            'file' => $file,
            'member' => $member,
        ]);
    }

    /**
     * Revoke a shared link.
     *
     * @param string $url Shared link URL to revoke
     * @return array Response status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-revoke_shared_link
     */
    public function revokeSharedLink(string $url): array
    {
        return $this->client->rpcRequest('/sharing/revoke_shared_link', [
            'url' => $url,
        ]);
    }

    /**
     * Share a folder.
     *
     * @param string $path Path to the folder
     * @param string|null $aclUpdatePolicy ACL update policy
     * @param bool $forceAsync Force async processing
     * @param string|null $memberPolicy Member policy
     * @param string|null $sharedLinkPolicy Shared link policy
     * @param array $viewerInfoPolicy Viewer info policy
     * @param string|null $accessInheritance Access inheritance
     * @param array $actions Actions configuration
     * @param string|null $linkSettings Link settings
     * @return array Shared folder metadata or async job ID
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-share_folder
     */
    public function shareFolder(
        string $path,
        ?string $aclUpdatePolicy = null,
        bool $forceAsync = false,
        ?string $memberPolicy = null,
        ?string $sharedLinkPolicy = null,
        array $viewerInfoPolicy = [],
        ?string $accessInheritance = null,
        array $actions = [],
        ?string $linkSettings = null
    ): array {
        $params = [
            'path' => $path,
            'force_async' => $forceAsync,
        ];

        if ($aclUpdatePolicy) {
            $params['acl_update_policy'] = ['.tag' => $aclUpdatePolicy];
        }

        if ($memberPolicy) {
            $params['member_policy'] = ['.tag' => $memberPolicy];
        }

        if ($sharedLinkPolicy) {
            $params['shared_link_policy'] = ['.tag' => $sharedLinkPolicy];
        }

        if (!empty($viewerInfoPolicy)) {
            $params['viewer_info_policy'] = $viewerInfoPolicy;
        }

        if ($accessInheritance) {
            $params['access_inheritance'] = ['.tag' => $accessInheritance];
        }

        if (!empty($actions)) {
            $params['actions'] = $actions;
        }

        if ($linkSettings) {
            $params['link_settings'] = $linkSettings;
        }

        return $this->client->rpcRequest('/sharing/share_folder', $params);
    }

    /**
     * Transfer ownership of a shared folder.
     *
     * @param string $sharedFolderId Shared folder ID
     * @param string $toDropboxId New owner's Dropbox ID
     * @return array Response status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-transfer_folder
     */
    public function transferFolder(string $sharedFolderId, string $toDropboxId): array
    {
        return $this->client->rpcRequest('/sharing/transfer_folder', [
            'shared_folder_id' => $sharedFolderId,
            'to_dropbox_id' => $toDropboxId,
        ]);
    }

    /**
     * Unmount a shared folder.
     *
     * @param string $sharedFolderId Shared folder ID
     * @return array Response status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-unmount_folder
     */
    public function unmountFolder(string $sharedFolderId): array
    {
        return $this->client->rpcRequest('/sharing/unmount_folder', [
            'shared_folder_id' => $sharedFolderId,
        ]);
    }

    /**
     * Unshare a folder.
     *
     * @param string $sharedFolderId Shared folder ID
     * @param bool $leaveACopy Leave a copy of the folder
     * @return array Async job ID
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-unshare_folder
     */
    public function unshareFolder(string $sharedFolderId, bool $leaveACopy = false): array
    {
        return $this->client->rpcRequest('/sharing/unshare_folder', [
            'shared_folder_id' => $sharedFolderId,
            'leave_a_copy' => $leaveACopy,
        ]);
    }

    /**
     * Unshare a file.
     *
     * @param string $file File path or ID
     * @return array Response status
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-unshare_file
     */
    public function unshareFile(string $file): array
    {
        return $this->client->rpcRequest('/sharing/unshare_file', [
            'file' => $file,
        ]);
    }

    /**
     * Update folder member permissions.
     *
     * @param string $sharedFolderId Shared folder ID
     * @param array $member Member to update
     * @param string $accessLevel New access level
     * @return array Updated member information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-update_folder_member
     */
    public function updateFolderMember(string $sharedFolderId, array $member, string $accessLevel): array
    {
        return $this->client->rpcRequest('/sharing/update_folder_member', [
            'shared_folder_id' => $sharedFolderId,
            'member' => $member,
            'access_level' => ['.tag' => $accessLevel],
        ]);
    }

    /**
     * Update file member permissions.
     *
     * @param string $file File path or ID
     * @param array $member Member to update
     * @param string $accessLevel New access level
     * @return array Updated member information
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-update_file_member
     */
    public function updateFileMember(string $file, array $member, string $accessLevel): array
    {
        return $this->client->rpcRequest('/sharing/update_file_member', [
            'file' => $file,
            'member' => $member,
            'access_level' => ['.tag' => $accessLevel],
        ]);
    }

    /**
     * Update folder policy.
     *
     * @param string $sharedFolderId Shared folder ID
     * @param array $policies New policies
     * @return array Updated folder metadata
     * @throws DropboxException
     * @link https://www.dropbox.com/developers/documentation/http/documentation#sharing-update_folder_policy
     */
    public function updateFolderPolicy(string $sharedFolderId, array $policies): array
    {
        return $this->client->rpcRequest('/sharing/update_folder_policy', array_merge(
            ['shared_folder_id' => $sharedFolderId],
            $policies
        ));
    }

    /**
     * Convert a Dropbox shared link to a direct link for image hosting.
     * 
     * This method converts a standard Dropbox shared link into a direct link
     * that can be used to embed images in HTML, Markdown, or other content.
     * 
     * Supports two conversion methods:
     * 1. 'raw' - Changes ?dl=0 to ?raw=1
     * 2. 'userusercontent' - Replaces www.dropbox.com with dl.dropboxusercontent.com
     *
     * @param string $url Dropbox shared link (e.g., https://www.dropbox.com/s/abcd1234/image.jpg?dl=0)
     * @param string $method Conversion method: 'raw' or 'userusercontent' (default: 'userusercontent')
     * @return string Direct link for image hosting
     * @throws \InvalidArgumentException If URL is invalid or method is not supported
     * 
     * @example
     * // Using userusercontent method (recommended)
     * $directLink = $client->sharing->convertToDirectLink(
     *     'https://www.dropbox.com/s/abcd1234/image.jpg?dl=0'
     * );
     * // Returns: https://dl.dropboxusercontent.com/s/abcd1234/image.jpg
     * 
     * // Using raw method
     * $directLink = $client->sharing->convertToDirectLink(
     *     'https://www.dropbox.com/s/abcd1234/image.jpg?dl=0',
     *     'raw'
     * );
     * // Returns: https://www.dropbox.com/s/abcd1234/image.jpg?raw=1
     */
    public function convertToDirectLink(string $url, string $method = 'userusercontent'): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL provided');
        }

        if (!str_contains($url, 'dropbox.com')) {
            throw new \InvalidArgumentException('URL must be a Dropbox link');
        }

        return match ($method) {
            'raw' => $this->convertToRawLink($url),
            'userusercontent' => $this->convertToUsercontentLink($url),
            default => throw new \InvalidArgumentException(
                "Invalid conversion method. Use 'raw' or 'userusercontent'"
            ),
        };
    }

    /**
     * Convert Dropbox link by replacing ?dl=0 with ?raw=1.
     * 
     * This method changes the download parameter to raw, which forces
     * the browser to display the file directly instead of showing
     * the Dropbox preview page.
     *
     * @param string $url Dropbox shared link
     * @return string Direct link with ?raw=1 parameter
     */
    public function convertToRawLink(string $url): string
    {
        $url = str_replace('?dl=0', '?raw=1', $url);
        $url = str_replace('&dl=0', '&raw=1', $url);
        
        if (!str_contains($url, '?raw=1') && !str_contains($url, '&raw=1')) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . 'raw=1';
        }
        
        return $url;
    }

    /**
     * Convert Dropbox link by replacing www.dropbox.com with dl.dropboxusercontent.com.
     * 
     * This method uses Dropbox's content delivery domain which serves
     * files directly without any preview interface. This is the recommended
     * method for image hosting as it provides cleaner URLs.
     *
     * @param string $url Dropbox shared link
     * @return string Direct link using dl.dropboxusercontent.com domain
     */
    public function convertToUsercontentLink(string $url): string
    {
        $url = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $url);
        $url = str_replace('dropbox.com', 'dl.dropboxusercontent.com', $url);
        
        $url = preg_replace('/\?dl=\d+/', '', $url);
        $url = preg_replace('/&dl=\d+/', '', $url);
        
        return rtrim($url, '?&');
    }

    /**
     * Create a shared link and immediately convert it to a direct link.
     * 
     * This is a convenience method that combines creating a shared link
     * and converting it to a direct link in one call.
     *
     * @param string $path Path to the file in Dropbox
     * @param array $settings Optional link settings
     * @param string $method Conversion method: 'raw' or 'userusercontent'
     * @return array Array with 'url' (original), 'direct_url' (converted), and full metadata
     * @throws DropboxException
     * 
     * @example
     * $result = $client->sharing->createDirectLink('/Photos/vacation.jpg');
     * echo '<img src="' . $result['direct_url'] . '" alt="Vacation">';
     */
    public function createDirectLink(
        string $path,
        array $settings = [],
        string $method = 'userusercontent'
    ): array {
        $linkData = $this->createSharedLinkWithSettings($path, $settings);
        
        $linkData['direct_url'] = $this->convertToDirectLink($linkData['url'], $method);
        
        return $linkData;
    }
}
