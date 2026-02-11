# Dropbox PHP SDK

![Dropbox PHP SDK](https://github.com/user-attachments/assets/361952c5-03d0-4ef6-b0b8-3106bb4ca3be)

[![Latest Version](https://img.shields.io/packagist/v/tigusigalpa/dropbox-php.svg)](https://packagist.org/packages/tigusigalpa/dropbox-php)
[![License](https://img.shields.io/packagist/l/tigusigalpa/dropbox-php.svg)](https://github.com/tigusigalpa/dropbox-php/blob/main/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/tigusigalpa/dropbox-php.svg)](https://packagist.org/packages/tigusigalpa/dropbox-php)

PHP SDK for [Dropbox API v2](https://www.dropbox.com/developers/documentation/http/documentation) with Laravel 8-12 support.
Provides a type-safe interface for working with Dropbox storage, file sharing, and collaboration from PHP 8.1+ applications.
Can be used standalone or as a Laravel package with auto-registered service provider and facade.

## What's Inside

The package covers all major Dropbox API v2 endpoints: files, sharing, users, file requests, and Paper. It handles OAuth 2.0 flows, chunked uploads for large files, and batch operations. Error handling is built around a dedicated exception class with access to Dropbox error details.

Laravel users get a service provider, facade, and config publishing out of the box. Everyone else can use `DropboxClient` directly — no framework required.

**🌐 Language:** English | [Русский](README-ru.md)

## Table of Contents

- [Features](#features)
- [Supported Endpoints](#supported-endpoints)
- [What's Inside](#whats-inside)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Detailed Usage Examples](#detailed-usage-examples)
    - [Working with Files](#working-with-files)
    - [Sharing](#sharing)
    - [Users and Account](#users-and-account)
    - [File Requests](#file-requests)
    - [Paper Documents](#paper-documents)
    - [Batch Operations](#batch-operations)
- [OAuth 2.0 Authorization](#oauth-20-authorization)
- [Laravel Usage](#laravel-usage)
- [Error Handling](#error-handling)
- [Advanced Examples](#advanced-examples)
- [Package Structure](#package-structure)
- [Testing](#testing)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [License](#license)

## Features

- ✅ **Dropbox API v2** — all major endpoints covered
- 🚀 **Laravel 8–12** — service provider, facade, config publishing
- 🎯 **PHP 8.1+** — typed properties, enums, named arguments
- 📦 **Standalone** — works without any framework
- 🔐 **OAuth 2.0** — authorization URL, token exchange, refresh
- 📝 **Documentation** — usage examples and API reference
- 🧪 **Tests** — PHPUnit test suite included
- 🎨 **Clean API** — `$client->files->upload(...)` style
- ⚡ **Chunked Upload** — large files uploaded in configurable chunks
- 🔄 **Batch Operations** — copy, move, delete up to 1000 files per call

## Supported Endpoints

- **Files** - Upload, download, move, copy, delete, search, and manage files/folders
- **Sharing** - Create shared links, manage folder/file sharing, collaborate with team members
- **Users** - Get account information, space usage, and user features
- **File Requests** - Create and manage file request forms
- **Paper** - Create, edit, and manage Dropbox Paper documents
- **Check** - API connectivity and health checks

## Requirements

- PHP 8.1 or higher
- Guzzle HTTP client 7.0+
- Laravel 8.0+ (optional, for Laravel integration)

## Installation

Install the package via Composer:

```bash
composer require tigusigalpa/dropbox-php
```

### Laravel Installation

The package will automatically register its service provider and facade.

Publish the configuration file:

```bash
php artisan vendor:publish --tag=dropbox-config
```

Add your Dropbox credentials to your `.env` file:

```env
DROPBOX_ACCESS_TOKEN=your_access_token_here
DROPBOX_APP_KEY=your_app_key
DROPBOX_APP_SECRET=your_app_secret
DROPBOX_REDIRECT_URI=https://your-app.com/callback
```

## Quick Start

### Obtaining Access Token

1. Create a Dropbox app at [Dropbox App Console](https://www.dropbox.com/developers/apps)
2. Choose your app type and permissions
3. Generate an access token from the app settings page

For production applications, implement the OAuth 2.0 flow (see [OAuth 2.0 Authorization](#oauth-20-authorization)
section).

### Basic Usage (Standalone PHP)

```php
use Tigusigalpa\Dropbox\DropboxClient;

$client = new DropboxClient('your_access_token');

// Get current user account info
$account = $client->users->getCurrentAccount();
echo "Hello, " . $account['name']['display_name'];

// Upload a file
$result = $client->files->upload(
    '/Documents/hello.txt',
    'Hello, Dropbox!',
    'add'
);

// List folder contents
$contents = $client->files->listFolder('/Documents');
foreach ($contents['entries'] as $entry) {
    echo $entry['name'] . "\n";
}

// Download a file
$file = $client->files->download('/Documents/hello.txt');
file_put_contents('local-hello.txt', $file['content']);

// Create a shared link
$link = $client->sharing->createSharedLinkWithSettings('/Documents/hello.txt');
echo "Share this link: " . $link['url'];
```

### Laravel Usage

```php
use Tigusigalpa\Dropbox\Facades\Dropbox;

// Using Facade
$account = Dropbox::users()->getCurrentAccount();

// Using Dependency Injection
use Tigusigalpa\Dropbox\DropboxClient;

class FileController extends Controller
{
    public function upload(Request $request, DropboxClient $dropbox)
    {
        $content = file_get_contents($request->file('document')->path());
        
        $result = $dropbox->files->upload(
            '/uploads/' . $request->file('document')->getClientOriginalName(),
            $content
        );
        
        return response()->json($result);
    }
}
```

## Detailed Usage Examples

### Working with Files

#### Uploading Files

```php
// Simple upload
$client->files->upload('/path/to/file.txt', 'File content');

// Upload with options
$client->files->upload(
    '/path/to/file.txt',
    $content,
    'overwrite',  // mode: 'add', 'overwrite', or 'update'
    true,         // autorename if conflict
    false,        // mute notifications
    false         // strict conflict checking
);

// Upload from local file
$content = file_get_contents('/local/path/file.pdf');
$client->files->upload('/Dropbox/file.pdf', $content);

// Upload with metadata
$result = $client->files->upload(
    '/Documents/report.pdf',
    file_get_contents('local-report.pdf'),
    'add',
    false,
    false,
    false
);
echo "Uploaded file: {$result['name']}, size: {$result['size']} bytes";
```

#### Large File Upload (Chunked)

```php
// Start upload session
$session = $client->files->uploadSessionStart($firstChunk, false);
$sessionId = $session['session_id'];

// Append chunks
$offset = strlen($firstChunk);
$client->files->uploadSessionAppend($sessionId, $offset, $secondChunk, false);

// Finish upload
$offset += strlen($secondChunk);
$result = $client->files->uploadSessionFinish(
    $sessionId,
    $offset,
    $lastChunk,
    ['path' => '/large-file.zip', 'mode' => 'add']
);

// Complete example for uploading a large file
$filePath = '/path/to/large-video.mp4';
$chunkSize = 4 * 1024 * 1024; // 4MB chunks
$file = fopen($filePath, 'rb');

// First chunk
$firstChunk = fread($file, $chunkSize);
$session = $client->files->uploadSessionStart($firstChunk, false);
$offset = strlen($firstChunk);

// Remaining chunks
while (!feof($file)) {
    $chunk = fread($file, $chunkSize);
    $isLast = feof($file);
    
    if ($isLast) {
        // Last chunk - finish session
        $client->files->uploadSessionFinish(
            $session['session_id'],
            $offset,
            $chunk,
            ['path' => '/Videos/large-video.mp4', 'mode' => 'add']
        );
    } else {
        // Intermediate chunk
        $client->files->uploadSessionAppend(
            $session['session_id'],
            $offset,
            $chunk,
            false
        );
        $offset += strlen($chunk);
    }
}
fclose($file);
```

#### Downloading Files

```php
// Download file
$file = $client->files->download('/Documents/report.pdf');
file_put_contents('local-report.pdf', $file['content']);
echo "Downloaded file size: " . strlen($file['content']) . " bytes";

// Download specific revision
$file = $client->files->download('/Documents/report.pdf', 'rev123abc');

// Download folder as ZIP
$zip = $client->files->downloadZip('/Documents/Project');
file_put_contents('project.zip', $zip['content']);

// Get temporary download link (valid for 4 hours)
$link = $client->files->getTemporaryLink('/Documents/report.pdf');
echo "Temporary link: " . $link['link'];

// Export file (e.g., Google Docs to PDF)
$exported = $client->files->export('/Documents/google-doc.gdoc', 'pdf');
file_put_contents('exported.pdf', $exported['content']);
```

#### File and Folder Management

```php
// Create folder
$folder = $client->files->createFolder('/Projects/NewProject');
echo "Created folder: " . $folder['metadata']['path_display'];

// Create multiple folders at once
$result = $client->files->createFolderBatch([
    '/Projects/Project1',
    '/Projects/Project2',
    '/Projects/Project3'
], false, false);

// Move file/folder
$moved = $client->files->move('/old/path.txt', '/new/path.txt');
echo "Moved to: " . $moved['metadata']['path_display'];

// Copy file/folder
$copied = $client->files->copy('/source.txt', '/destination.txt');

// Delete file/folder (to trash)
$client->files->delete('/path/to/delete.txt');

// Permanently delete (bypass trash)
$client->files->permanentlyDelete('/path/to/file.txt');

// Restore file to previous revision
$restored = $client->files->restore('/path/to/file.txt', 'rev123abc');

// Get file revision history
$revisions = $client->files->listRevisions('/Documents/important.docx', 'path', 100);
foreach ($revisions['entries'] as $rev) {
    echo "Revision: {$rev['rev']}, date: {$rev['client_modified']}\n";
}
```

#### Search and Listing

```php
// List folder contents
$result = $client->files->listFolder('/Documents');
foreach ($result['entries'] as $entry) {
    if ($entry['.tag'] === 'folder') {
        echo "Folder: " . $entry['name'] . "\n";
    } else {
        echo "File: " . $entry['name'] . " (" . $entry['size'] . " bytes)\n";
        echo "  Modified: " . $entry['client_modified'] . "\n";
    }
}

// List recursively
$result = $client->files->listFolder('', true);

// Continue listing with cursor (pagination)
if ($result['has_more']) {
    $more = $client->files->listFolderContinue($result['cursor']);
}

// Complete listing of large folder with pagination
$allEntries = [];
$result = $client->files->listFolder('/BigFolder');
$allEntries = array_merge($allEntries, $result['entries']);

while ($result['has_more']) {
    $result = $client->files->listFolderContinue($result['cursor']);
    $allEntries = array_merge($allEntries, $result['entries']);
}
echo "Total items: " . count($allEntries);

// Search files
$results = $client->files->search(
    'invoice',           // query
    '/Documents',        // path
    100,                 // max results
    'relevance',         // order by
    'active',            // file status
    null,                // filename only
    ['pdf', 'docx'],     // file extensions
    ['documents']        // file categories
);

foreach ($results['matches'] as $match) {
    $file = $match['metadata']['metadata'];
    echo "Found: {$file['name']} in {$file['path_display']}\n";
}

// Continue search with cursor
if ($results['has_more']) {
    $moreResults = $client->files->searchContinue($results['cursor']);
}

// Get file metadata
$metadata = $client->files->getMetadata('/Documents/file.txt', true);
echo "Modified: " . $metadata['client_modified'];
echo "Size: " . $metadata['size'] . " bytes";
echo "ID: " . $metadata['id'];

// Get metadata with media info
$metadata = $client->files->getMetadata('/Photos/vacation.jpg', true);
if (isset($metadata['media_info'])) {
    echo "Dimensions: {$metadata['media_info']['metadata']['dimensions']['width']}x";
    echo "{$metadata['media_info']['metadata']['dimensions']['height']}";
}
```

#### Thumbnails and Previews

```php
// Get thumbnail
$thumb = $client->files->getThumbnail(
    '/Photos/vacation.jpg',
    'jpeg',      // format: 'jpeg' or 'png'
    'w256h256',  // size: w32h32, w64h64, w128h128, w256h256, w480h320, w640h480, w960h640, w1024h768, w2048h1536
    'strict'     // mode: 'strict', 'bestfit', 'fitone_bestfit'
);
file_put_contents('thumb.jpg', $thumb['content']);

// Get preview
$preview = $client->files->getPreview('/Documents/presentation.pptx');
file_put_contents('preview.pdf', $preview['content']);

// Get thumbnails in batch
$thumbs = $client->files->getThumbnailBatch([
    [
        'path' => '/Photos/img1.jpg',
        'format' => 'jpeg',
        'size' => 'w128h128',
        'mode' => 'strict'
    ],
    [
        'path' => '/Photos/img2.jpg',
        'format' => 'jpeg',
        'size' => 'w128h128',
        'mode' => 'strict'
    ],
]);

foreach ($thumbs['entries'] as $entry) {
    if ($entry['.tag'] === 'success') {
        $thumbnail = $entry['thumbnail'];
        file_put_contents('thumb_' . basename($entry['metadata']['name']), $thumbnail);
    }
}
```

### Sharing

#### Shared Links

```php
// Create shared link
$link = $client->sharing->createSharedLinkWithSettings('/Documents/report.pdf', [
    'requested_visibility' => ['.tag' => 'public'],
    'audience' => ['.tag' => 'public'],
    'access' => ['.tag' => 'viewer'],
]);
echo "Share URL: " . $link['url'];

// Create link with password and expiration
$link = $client->sharing->createSharedLinkWithSettings('/Documents/secret.pdf', [
    'link_password' => 'mypassword123',
    'expires' => '2024-12-31T23:59:59Z',
]);

// List all shared links
$links = $client->sharing->listSharedLinks();

// Get shared link metadata
$metadata = $client->sharing->getSharedLinkMetadata('https://www.dropbox.com/...');

// Modify shared link settings
$updated = $client->sharing->modifySharedLinkSettings(
    'https://www.dropbox.com/...',
    ['requested_visibility' => ['.tag' => 'password']]
);

// Revoke shared link
$client->sharing->revokeSharedLink('https://www.dropbox.com/...');
```

#### Folder Sharing

```php
// Share a folder
$shared = $client->sharing->shareFolder('/Projects/TeamProject', null, false);
$folderId = $shared['shared_folder_id'];

// Add members to shared folder
$client->sharing->addFolderMember($folderId, [
    [
        'member' => ['.tag' => 'email', 'email' => 'colleague@example.com'],
        'access_level' => ['.tag' => 'editor'],
    ],
], false, 'Please review this project');

// List folder members
$members = $client->sharing->listFolderMembers($folderId);

// Update member permissions
$client->sharing->updateFolderMember(
    $folderId,
    ['.tag' => 'email', 'email' => 'colleague@example.com'],
    'viewer'
);

// Remove folder member
$client->sharing->removeFolderMember(
    $folderId,
    ['.tag' => 'email', 'email' => 'colleague@example.com'],
    true  // leave a copy
);

// List shared folders
$folders = $client->sharing->listFolders(100);

// Mount shared folder
$client->sharing->mountFolder($folderId);

// Unmount shared folder
$client->sharing->unmountFolder($folderId);

// Unshare folder
$client->sharing->unshareFolder($folderId, false);
```

#### File Sharing

```php
// Add file members
$client->sharing->addFileMember(
    '/Documents/contract.pdf',
    [
        [
            'member' => ['.tag' => 'email', 'email' => 'client@example.com'],
            'access_level' => ['.tag' => 'viewer'],
        ],
    ],
    'Please review and sign',
    false,
    'viewer'
);

// List file members
$members = $client->sharing->listFileMembers('/Documents/contract.pdf');

// Remove file member
$client->sharing->removeFileMember(
    '/Documents/contract.pdf',
    ['.tag' => 'email', 'email' => 'client@example.com']
);
```

### Users and Account

```php
// Get current account info
$account = $client->users->getCurrentAccount();
echo "Account ID: " . $account['account_id'];
echo "Name: " . $account['name']['display_name'];
echo "Email: " . $account['email'];
echo "Country: " . $account['country'];

// Get another user's account info
$user = $client->users->getAccount('dbid:AAH4f99T0taONIb-OurWxbNQ6ywGRopQngc');

// Get multiple users' info
$users = $client->users->getAccountBatch([
    'dbid:AAH4f99T0taONIb-OurWxbNQ6ywGRopQngc',
    'dbid:AAH1234567890abcdefghijklmnopqrst',
]);

// Get space usage
$space = $client->users->getSpaceUsage();
echo "Used: " . $space['used'] . " bytes\n";
echo "Allocated: " . $space['allocation']['allocated'] . " bytes\n";
$percentage = ($space['used'] / $space['allocation']['allocated']) * 100;
echo "Usage: " . number_format($percentage, 2) . "%\n";

// Check feature availability
$features = $client->users->getFeaturesValues([
    'paper_as_files',
    'file_locking',
]);
```

### File Requests

```php
// Create file request
$request = $client->fileRequests->create(
    'Upload your documents',
    '/File Requests/Documents',
    '2024-12-31T23:59:59Z',  // deadline
    true,                     // open
    'Please upload all required documents for the application'
);
echo "File request URL: " . $request['url'];

// Get file request
$request = $client->fileRequests->get('oaCAVmEyrqYnkZX9955Y');

// List all file requests
$requests = $client->fileRequests->list(1000);

// Update file request
$updated = $client->fileRequests->update('oaCAVmEyrqYnkZX9955Y', [
    'title' => 'Updated Title',
    'open' => false,
]);

// Delete file requests
$client->fileRequests->delete(['oaCAVmEyrqYnkZX9955Y']);

// Delete all closed file requests
$client->fileRequests->deleteAllClosed();

// Count file requests
$count = $client->fileRequests->count();
echo "Total file requests: " . $count['file_request_count'];
```

### Paper Documents

```php
// Create Paper document
$doc = $client->paper->docsCreate(
    '<h1>Meeting Notes</h1><p>Discussion points...</p>',
    'html'
);
$docId = $doc['doc_id'];

// Download Paper document
$content = $client->paper->docsDownload($docId, 'markdown');
file_put_contents('notes.md', $content['content']);

// Update Paper document
$client->paper->docsUpdate(
    $docId,
    '<h1>Updated Notes</h1><p>New content...</p>',
    'html',
    'append',
    1
);

// Get Paper document metadata
$metadata = $client->paper->docsGetMetadata($docId);

// List Paper documents
$docs = $client->paper->docsList('docs_accessed', 'modified', 'descending', 100);

// Share Paper document
$client->paper->docsUsersAdd($docId, [
    [
        'member' => ['.tag' => 'email', 'email' => 'team@example.com'],
        'permission_level' => ['.tag' => 'edit'],
    ],
]);

// List users with access
$users = $client->paper->docsUsersList($docId, 100);

// Remove users
$client->paper->docsUsersRemove($docId, [
    ['.tag' => 'email', 'email' => 'team@example.com'],
]);

// Delete Paper document
$client->paper->docsPermanentlyDelete($docId);
```

### Batch Operations

```php
// Copy multiple files
$job = $client->files->copyBatch([
    ['from_path' => '/file1.txt', 'to_path' => '/backup/file1.txt'],
    ['from_path' => '/file2.txt', 'to_path' => '/backup/file2.txt'],
]);

// Check batch job status
$status = $client->files->copyBatchCheck($job['async_job_id']);

// Move multiple files
$job = $client->files->moveBatch([
    ['from_path' => '/old/file1.txt', 'to_path' => '/new/file1.txt'],
    ['from_path' => '/old/file2.txt', 'to_path' => '/new/file2.txt'],
]);

// Delete multiple files
$job = $client->files->deleteBatch(['/file1.txt', '/file2.txt', '/file3.txt']);
```

### Save Files from URL

```php
// Save file from URL
$job = $client->files->saveUrl('/Downloads/image.jpg', 'https://example.com/image.jpg');

// Check save URL job status
$status = $client->files->saveUrlCheckJobStatus($job['async_job_id']);

if ($status['.tag'] === 'complete') {
    echo "File saved successfully!";
}
```

## OAuth 2.0 Authorization

### Authorization URL

```php
use Tigusigalpa\Dropbox\DropboxClient;

// Generate authorization URL
$authUrl = DropboxClient::getAuthorizationUrl(
    'your_app_key',
    'https://your-app.com/callback',
    'random_state_string',  // CSRF protection
    ['files.content.write', 'files.content.read']  // optional scopes
);

// Redirect user to authorization URL
header('Location: ' . $authUrl);
```

### Exchange Code for Token

```php
// In your callback route
$code = $_GET['code'];
$state = $_GET['state'];

// Verify state parameter (CSRF protection)
if ($state !== $_SESSION['oauth_state']) {
    die('Invalid state parameter');
}

// Exchange code for access token
$tokenData = DropboxClient::getAccessToken(
    $code,
    'your_app_key',
    'your_app_secret',
    'https://your-app.com/callback'
);

// Store tokens securely
$accessToken = $tokenData['access_token'];
$refreshToken = $tokenData['refresh_token'] ?? null;

// Create client with new token
$client = new DropboxClient($accessToken);
```

### Laravel OAuth Example

```php
// routes/web.php
Route::get('/dropbox/auth', [DropboxController::class, 'redirectToDropbox']);
Route::get('/dropbox/callback', [DropboxController::class, 'handleCallback']);

// app/Http/Controllers/DropboxController.php
use Tigusigalpa\Dropbox\DropboxClient;

class DropboxController extends Controller
{
    public function redirectToDropbox()
    {
        $state = Str::random(40);
        session(['dropbox_state' => $state]);
        
        $url = DropboxClient::getAuthorizationUrl(
            config('dropbox.app_key'),
            config('dropbox.redirect_uri'),
            $state
        );
        
        return redirect($url);
    }
    
    public function handleCallback(Request $request)
    {
        if ($request->state !== session('dropbox_state')) {
            abort(403, 'Invalid state');
        }
        
        $tokenData = DropboxClient::getAccessToken(
            $request->code,
            config('dropbox.app_key'),
            config('dropbox.app_secret'),
            config('dropbox.redirect_uri')
        );
        
        // Store tokens for the user
        auth()->user()->update([
            'dropbox_access_token' => encrypt($tokenData['access_token']),
            'dropbox_refresh_token' => encrypt($tokenData['refresh_token'] ?? null),
        ]);
        
        return redirect('/dashboard')->with('success', 'Dropbox connected!');
    }
}
```

### Refresh Token

```php
// Refresh access token when expired
$newTokenData = DropboxClient::refreshAccessToken(
    $refreshToken,
    'your_app_key',
    'your_app_secret'
);

$newAccessToken = $newTokenData['access_token'];

// Update client token
$client->setAccessToken($newAccessToken);
```

## Error Handling

```php
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

try {
    $result = $client->files->upload('/test.txt', 'content');
} catch (DropboxException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $e->getCode() . "\n";
    
    // Get detailed error information
    $response = $e->getResponse();
    if ($response) {
        echo "Error Summary: " . $e->getErrorSummary() . "\n";
        echo "Error Tag: " . $e->getErrorTag() . "\n";
        print_r($response);
    }
}
```

## Advanced Examples

### Custom HTTP Client Configuration

```php
use GuzzleHttp\Client as GuzzleClient;
use Tigusigalpa\Dropbox\DropboxClient;

// Create custom Guzzle client
$guzzle = new GuzzleClient([
    'timeout' => 60,
    'verify' => true,
    'proxy' => 'http://proxy.example.com:8080',
]);

// Note: Currently the package creates its own Guzzle instance
// For custom configuration, you may need to extend the DropboxClient class
```

### Working with Cursors (Pagination)

```php
// List all files in a large folder
$cursor = null;
$allFiles = [];

do {
    if ($cursor === null) {
        $result = $client->files->listFolder('/LargeFolder');
    } else {
        $result = $client->files->listFolderContinue($cursor);
    }
    
    $allFiles = array_merge($allFiles, $result['entries']);
    $cursor = $result['cursor'];
} while ($result['has_more']);

echo "Total files: " . count($allFiles);
```

### Monitoring Folder Changes

```php
// Get initial cursor
$cursor = $client->files->listFolderGetLatestCursor('/MonitoredFolder', true);
$cursorValue = $cursor['cursor'];

// Later, check for changes
$changes = $client->files->listFolderLongpoll($cursorValue, 30);

if ($changes['changes']) {
    // Get the actual changes
    $result = $client->files->listFolderContinue($cursorValue);
    
    foreach ($result['entries'] as $entry) {
        echo "Changed: " . $entry['name'] . "\n";
    }
}
```

## Performance and Optimization

### Efficient File Operations

- **Chunked uploads** — large files are split into chunks (default 4MB, configurable) to avoid memory and timeout issues
- **Batch endpoints** — copy, move, delete up to 1000 files in a single API call
- **Persistent connections** — Guzzle keeps connections alive between requests
- **Stream-based downloads** — files are not loaded entirely into memory

### Caching Strategies

```php
// Cache folder listings to reduce API calls
$cacheKey = 'dropbox_folder_' . md5($path);
$contents = Cache::remember($cacheKey, 3600, function() use ($client, $path) {
    return $client->files->listFolder($path);
});

// Cache shared links
$linkCache = Cache::remember('dropbox_link_' . $fileId, 86400, function() use ($client, $path) {
    return $client->sharing->createSharedLinkWithSettings($path);
});
```

### Rate Limiting

Dropbox API has rate limits. When you hit them, the SDK throws an exception with code 429:

```php
try {
    $result = $client->files->upload($path, $content);
} catch (DropboxException $e) {
    if ($e->getCode() === 429) {
        // Rate limited - wait and retry
        $retryAfter = $e->getResponse()['retry_after'] ?? 60;
        sleep($retryAfter);
        $result = $client->files->upload($path, $content);
    }
}
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

## API Reference

For complete API documentation, visit:

- [Dropbox HTTP API Documentation](https://www.dropbox.com/developers/documentation/http/documentation)
- [Dropbox API Explorer](https://dropbox.github.io/dropbox-api-v2-explorer/)

## Common Use Cases

### Backup System

```php
// Backup local files to Dropbox
$backupFolder = '/Backups/' . date('Y-m-d');
$client->files->createFolder($backupFolder);

$files = glob('/var/www/app/storage/backups/*.sql');
foreach ($files as $file) {
    $content = file_get_contents($file);
    $client->files->upload(
        $backupFolder . '/' . basename($file),
        $content
    );
}
```

### File Sync

```php
// Sync local folder with Dropbox
$localPath = '/local/documents';
$remotePath = '/Documents';

$localFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($localPath)
);

foreach ($localFiles as $file) {
    if ($file->isFile()) {
        $relativePath = str_replace($localPath, '', $file->getPathname());
        $content = file_get_contents($file->getPathname());
        
        $client->files->upload(
            $remotePath . $relativePath,
            $content,
            'overwrite'
        );
    }
}
```

### Image Gallery

```php
// Create image gallery with thumbnails
$photos = $client->files->listFolder('/Photos');

foreach ($photos['entries'] as $photo) {
    if ($photo['.tag'] === 'file') {
        // Get thumbnail
        $thumb = $client->files->getThumbnail(
            $photo['path_display'],
            'jpeg',
            'w256h256'
        );
        
        // Save thumbnail
        file_put_contents(
            'thumbs/' . $photo['name'],
            $thumb['content']
        );
        
        // Create shared link for full image
        $link = $client->sharing->createSharedLinkWithSettings(
            $photo['path_display']
        );
        
        echo '<img src="thumbs/' . $photo['name'] . '" data-full="' . $link['url'] . '">';
    }
}
```

## Package Structure

### Core Components

```
dropbox-php/
├── config/
│   └── dropbox.php             # Laravel configuration
├── examples/
│   ├── basic-usage.php         # Standalone PHP usage examples
│   ├── laravel-usage.php       # Laravel integration examples
│   └── oauth-flow.php          # OAuth 2.0 flow implementation
├── src/
│   ├── Endpoints/              # API endpoint implementations
│   │   ├── Check.php           # API health checks
│   │   ├── FileRequests.php    # File request operations
│   │   ├── Files.php           # File/folder operations (40+ methods)
│   │   ├── Paper.php           # Dropbox Paper operations
│   │   ├── Sharing.php         # Sharing and collaboration (30+ methods)
│   │   └── Users.php           # User account operations
│   ├── Exceptions/
│   │   └── DropboxException.php # Custom exception class
│   ├── Facades/
│   │   └── Dropbox.php         # Laravel facade
│   ├── DropboxClient.php       # Main client class
│   └── DropboxServiceProvider.php # Laravel service provider
└── tests/                      # PHPUnit tests
```

### Usage Patterns

**Standalone PHP:**

```php
$client = new DropboxClient($accessToken);
$result = $client->files->upload('/path/file.txt', $content);
```

**Laravel Facade:**

```php
use Tigusigalpa\Dropbox\Facades\Dropbox;
$result = Dropbox::files()->upload('/path/file.txt', $content);
```

**Laravel Dependency Injection:**

```php
public function upload(DropboxClient $dropbox) {
    $result = $dropbox->files->upload('/path/file.txt', $content);
}
```

## Testing

The package ships with:

- Unit tests for core functionality
- Integration test examples
- GitHub Actions CI/CD workflow
- PHPUnit configuration

Run tests:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

### Setup for Testing

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/YOUR_USERNAME/dropbox-php.git
   cd dropbox-php
   ```

3. Install dependencies:
   ```bash
   composer install
   ```

4. Create a `.env` file with your Dropbox credentials for testing:
   ```env
   DROPBOX_ACCESS_TOKEN=your_test_token
   ```

## Contributing

Contributions are welcome. Please follow these guidelines:

### Coding Standards

- Follow PSR-12 coding standards
- Write clear, descriptive commit messages
- Add PHPDoc blocks for all public methods
- Use type hints for parameters and return types
- Keep methods focused and single-purpose

### Pull Request Process

1. Create a new branch for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes and commit:
   ```bash
   git commit -m "Add feature: description"
   ```

3. Push to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

4. Create a Pull Request on GitHub

5. Ensure all tests pass and code follows standards

### Adding New Features

When adding new Dropbox API endpoints:

1. Create or update the appropriate endpoint class in `src/Endpoints/`
2. Add comprehensive PHPDoc comments
3. Include links to official Dropbox API documentation
4. Add usage examples to README.md
5. Write tests for the new functionality

### Reporting Issues

- Use the GitHub issue tracker
- Include PHP version, Laravel version (if applicable)
- Provide code examples that reproduce the issue
- Include error messages and stack traces

## Troubleshooting Guide

### Common Issues and Solutions

#### Authentication Errors

**Problem:** "Invalid access token" error

**Solutions:**
- Verify your access token is correct and not expired
- For OAuth tokens, implement refresh token logic
- Check that your app has the required permissions/scopes
- Ensure the token hasn't been revoked in Dropbox App Console

```php
// Check token validity
try {
    $account = $client->users->getCurrentAccount();
    echo "Token is valid for: " . $account['email'];
} catch (DropboxException $e) {
    if ($e->getCode() === 401) {
        // Token invalid - need to refresh or re-authenticate
        $newToken = DropboxClient::refreshAccessToken($refreshToken, $appKey, $appSecret);
    }
}
```

#### File Upload Failures

**Problem:** Upload fails for large files or times out

**Solutions:**
- Use chunked upload for files larger than 150MB
- Increase PHP `max_execution_time` and `memory_limit`
- Implement retry logic for network failures
- Check file path format (must start with /)

```php
// Robust upload with retry
$maxRetries = 3;
$attempt = 0;

while ($attempt < $maxRetries) {
    try {
        $result = $client->files->upload($path, $content);
        break;
    } catch (DropboxException $e) {
        $attempt++;
        if ($attempt >= $maxRetries) throw $e;
        sleep(2 ** $attempt); // Exponential backoff
    }
}
```

#### Path Errors

**Problem:** "Path not found" or "Malformed path" errors

**Solutions:**
- Ensure paths start with `/` (e.g., `/Documents/file.txt`)
- Use proper encoding for special characters
- Check that parent folders exist before creating files
- Verify case sensitivity (Dropbox paths are case-insensitive but case-preserving)

```php
// Ensure parent folder exists
$filePath = '/Documents/Reports/2024/report.pdf';
$parentPath = dirname($filePath);

try {
    $client->files->getMetadata($parentPath);
} catch (DropboxException $e) {
    // Parent doesn't exist - create it
    $client->files->createFolder($parentPath);
}

$client->files->upload($filePath, $content);
```

#### Laravel Integration Issues

**Problem:** Service provider not loading or facade not working

**Solutions:**
- Clear Laravel cache: `php artisan cache:clear`
- Clear config cache: `php artisan config:clear`
- Republish config: `php artisan vendor:publish --tag=dropbox-config --force`
- Verify `.env` variables are set correctly
- Check that package is in `composer.json` require section

```bash
# Complete Laravel reset
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

### Debug Mode

Enable detailed error logging:

```php
try {
    $result = $client->files->upload($path, $content);
} catch (DropboxException $e) {
    // Log detailed error information
    Log::error('Dropbox API Error', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'error_summary' => $e->getErrorSummary(),
        'error_tag' => $e->getErrorTag(),
        'response' => $e->getResponse(),
        'trace' => $e->getTraceAsString()
    ]);
}
```

## Frequently Asked Questions (FAQ)

### General Questions

**Q: Is this library production-ready?**

A: Yes. It has error handling, test coverage, and follows PSR-12. Used in production by several projects.

**Q: What's the difference between this and the official Dropbox SDK?**

A: This package targets PHP 8.1+, ships with Laravel integration (service provider, facade, config), and provides a simpler method-based API. The official SDK is more low-level.

**Q: Can I use this without Laravel?**

A: Yes, works as a standalone PHP library. Laravel integration is optional.

**Q: Does it support Dropbox Business/Team accounts?**

A: Yes, both personal and business accounts are supported. Use team-scoped access tokens for team operations.

### Technical Questions

**Q: What's the maximum file size I can upload?**

A: Up to 350GB with chunked upload. Files under 150MB can use the simple `upload()` method. Larger files go through upload sessions.

**Q: How do I handle rate limiting?**

A: The SDK throws `DropboxException` with code 429. Implement exponential backoff as shown in the troubleshooting section.

**Q: Can I upload files from URLs directly to Dropbox?**

A: Yes, `saveUrl()` tells Dropbox to fetch the file from a URL on its side.

**Q: How do I get a permanent link to a file?**

A: `createSharedLinkWithSettings()` gives you a permanent link. For short-lived links (4 hours), use `getTemporaryLink()`.

**Q: Does it support Dropbox Paper?**

A: Yes, the Paper endpoint covers creating, editing, sharing, and deleting Paper documents.

**Q: How do I handle file conflicts?**

A: Pass the `mode` parameter: `'add'` (fail if exists), `'overwrite'` (replace), or `'update'` (update a specific revision).

### Security Questions

**Q: How should I store access tokens?**

A: Don't store tokens in plain text. Use `encrypt()` in Laravel, environment variables, or a secrets manager. In production, use OAuth with refresh tokens.

**Q: Is it safe to use in multi-tenant applications?**

A: Yes. Create a separate `DropboxClient` per user with their own token. Don't share tokens between users.

**Q: How do I revoke access?**

A: Revoke tokens via the Dropbox App Console or build a revocation flow in your app settings.

## Best Practices

### Security Best Practices

1. **Never hardcode access tokens** - Use environment variables or secure configuration management
2. **Implement OAuth 2.0 flow** - For production applications, use proper OAuth instead of generated tokens
3. **Use refresh tokens** - Implement token refresh logic to maintain long-term access
4. **Validate user input** - Sanitize file paths and names before passing to API
5. **Implement rate limiting** - Add application-level rate limiting to prevent API abuse
6. **Log security events** - Track authentication failures and suspicious activities
7. **Use HTTPS only** - Ensure all callbacks and webhooks use HTTPS
8. **Scope permissions appropriately** - Request only the OAuth scopes your application needs

### Development Best Practices

1. **Use type hints** - Leverage PHP 8.1+ type system for better code quality
2. **Handle exceptions properly** - Always wrap API calls in try-catch blocks
3. **Implement retry logic** - Handle transient failures with exponential backoff
4. **Cache API responses** - Reduce API calls by caching folder listings and metadata
5. **Use batch operations** - Process multiple files efficiently with batch endpoints
6. **Test with real data** - Create a test Dropbox account for development
7. **Monitor API usage** - Track API call volumes to stay within rate limits
8. **Version your code** - Use semantic versioning and maintain changelog

### Performance Best Practices

1. **Use chunked uploads** - For files over 150MB, always use upload sessions
2. **Implement pagination** - Handle large folder listings with cursor-based pagination
3. **Stream large downloads** - Don't load entire files into memory
4. **Optimize search queries** - Use specific paths and filters to reduce result sets
5. **Leverage cursors** - Use `listFolderContinue()` for efficient pagination
6. **Batch thumbnail requests** - Get multiple thumbnails in one API call
7. **Use temporary links** - For public file access, temporary links are faster than downloads
8. **Implement queue workers** - Process large file operations asynchronously in Laravel

### Laravel-Specific Best Practices

```php
// Use queued jobs for large operations
class UploadToDropboxJob implements ShouldQueue
{
    public function handle(DropboxClient $dropbox)
    {
        $dropbox->files->upload($this->path, $this->content);
    }
}

// Use events for file operations
event(new FileUploadedToDropbox($filePath, $metadata));

// Implement middleware for Dropbox webhooks
Route::post('/dropbox/webhook', [DropboxWebhookController::class, 'handle'])
    ->middleware('verify.dropbox.signature');
```

## Comparison with Official Dropbox SDK

| Feature | This SDK | Official Dropbox SDK |
|---------|----------|---------------------|
| PHP Version | 8.1+ | 7.4+ |
| Laravel Integration | Built-in (provider, facade) | Manual |
| API v2 Coverage | All major endpoints | All endpoints |
| Documentation | Examples + API reference | API reference |
| Type Safety | Full type hints | Partial |
| Error Handling | Dedicated exception class | Basic exceptions |
| Chunked Upload | Built-in helpers | Manual implementation |
| Batch Operations | Supported | Supported |
| OAuth 2.0 Helpers | Included | Manual |

### Migration from Other Libraries

Method names map closely to the Dropbox HTTP API documentation:

```php
// Old library (example)
$dropbox->uploadFile('/path', $content);

// This SDK
$client->files->upload('/path', $content);

// Old library
$dropbox->getMetadata('/path');

// This SDK
$client->files->getMetadata('/path');
```

Method names follow the Dropbox API naming, so the [official HTTP docs](https://www.dropbox.com/developers/documentation/http/documentation) serve as a reference.

## Real-World Use Cases

### E-Commerce Platform

Store product images, invoices, and customer documents:

```php
// Upload product images with organized structure
$productId = 12345;
$imagePath = "/products/{$productId}/images/main.jpg";
$client->files->upload($imagePath, $imageContent);

// Generate shareable link for product image
$link = $client->sharing->createSharedLinkWithSettings($imagePath);
$product->image_url = $link['url'];
```

### Document Management System

Manage corporate documents with version control:

```php
// Upload document with metadata
$result = $client->files->upload(
    "/documents/contracts/{$contractId}.pdf",
    $pdfContent,
    'add'
);

// Track revisions
$revisions = $client->files->listRevisions($result['path_display']);

// Share with specific users
$client->sharing->addFileMember($result['path_display'], [
    ['member' => ['.tag' => 'email', 'email' => 'legal@company.com']]
]);
```

### Automated Backup System

Create scheduled backups of critical data:

```php
// Laravel scheduled task
protected function schedule(Schedule $schedule)
{
    $schedule->call(function (DropboxClient $dropbox) {
        $backupPath = '/backups/' . date('Y-m-d-H-i-s');
        $dropbox->files->createFolder($backupPath);
        
        // Backup database
        $dbBackup = Storage::get('backups/database.sql');
        $dropbox->files->upload("{$backupPath}/database.sql", $dbBackup);
        
        // Backup files
        $filesBackup = Storage::get('backups/files.tar.gz');
        $dropbox->files->upload("{$backupPath}/files.tar.gz", $filesBackup);
    })->daily();
}
```

### Media Gallery Application

Build a photo gallery with thumbnails:

```php
// Upload photos and generate thumbnails
foreach ($photos as $photo) {
    $path = "/gallery/{$albumId}/{$photo->name}";
    $client->files->upload($path, $photo->content);
    
    // Get thumbnail
    $thumb = $client->files->getThumbnail($path, 'jpeg', 'w256h256');
    Storage::put("thumbnails/{$photo->id}.jpg", $thumb['content']);
    
    // Create public link
    $link = $client->sharing->createSharedLinkWithSettings($path);
    $photo->public_url = $link['url'];
}
```

### Collaborative Workspace

Enable team collaboration with shared folders:

```php
// Create project workspace
$projectFolder = "/projects/{$projectName}";
$client->files->createFolder($projectFolder);

// Share with team
$shared = $client->sharing->shareFolder($projectFolder);

// Add team members
foreach ($teamMembers as $member) {
    $client->sharing->addFolderMember($shared['shared_folder_id'], [[
        'member' => ['.tag' => 'email', 'email' => $member->email],
        'access_level' => ['.tag' => $member->role === 'admin' ? 'editor' : 'viewer']
    ]]);
}
```

## Changelog

### Version 1.0.0 - 2024-12-20

**Added:**

- Initial release
- Full Dropbox API v2 support
- Files endpoint with complete file/folder operations
- Sharing endpoint for collaboration features
- Users endpoint for account management
- File Requests endpoint
- Paper endpoint for Dropbox Paper documents
- Check endpoint for API health checks
- Laravel 8-12 integration with service provider and facade
- OAuth 2.0 flow helpers
- Comprehensive documentation and examples
- PHPUnit test suite
- GitHub Actions CI/CD workflow

**Features:**

- Upload/download files with chunked upload support
- File and folder management (copy, move, delete, search)
- Shared links and folder sharing
- Batch operations support
- Thumbnail generation
- File preview and export
- Space usage tracking
- Error handling with detailed exceptions

## Security

If you discover any security-related issues, please email sovletig@gmail.com instead of using the issue tracker.

## Credits

- [Igor Sazonov](https://github.com/tigusigalpa)
- [All Contributors](https://github.com/tigusigalpa/dropbox-php/contributors)

## License

MIT License (MIT). Please see [LICENSE](LICENSE) file for more information.

## Links

- [GitHub Repository](https://github.com/tigusigalpa/dropbox-php)
- [Packagist](https://packagist.org/packages/tigusigalpa/dropbox-php)
- [Dropbox API Documentation](https://www.dropbox.com/developers/documentation/http/documentation)
- [Dropbox Developer Portal](https://www.dropbox.com/developers)
- [Dropbox App Console](https://www.dropbox.com/developers/apps)
- [Dropbox API Explorer](https://dropbox.github.io/dropbox-api-v2-explorer/)
