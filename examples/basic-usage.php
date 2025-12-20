<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\Dropbox\DropboxClient;
use Tigusigalpa\Dropbox\Exceptions\DropboxException;

$accessToken = getenv('DROPBOX_ACCESS_TOKEN') ?: 'your_access_token_here';
$client = new DropboxClient($accessToken);

try {
    echo "=== Dropbox PHP SDK - Basic Usage Example ===\n\n";

    echo "1. Getting account information...\n";
    $account = $client->users->getCurrentAccount();
    echo "   Account: {$account['name']['display_name']}\n";
    echo "   Email: {$account['email']}\n\n";

    echo "2. Getting space usage...\n";
    $space = $client->users->getSpaceUsage();
    $used = $space['used'];
    $allocated = $space['allocation']['allocated'];
    $percentage = ($used / $allocated) * 100;
    echo "   Used: " . number_format($used / 1024 / 1024, 2) . " MB\n";
    echo "   Total: " . number_format($allocated / 1024 / 1024, 2) . " MB\n";
    echo "   Usage: " . number_format($percentage, 2) . "%\n\n";

    echo "3. Creating a test folder...\n";
    $folderPath = '/SDK-Test-' . date('Y-m-d-His');
    $folder = $client->files->createFolder($folderPath);
    echo "   Created: {$folder['metadata']['path_display']}\n\n";

    echo "4. Uploading a test file...\n";
    $content = "Hello from Dropbox PHP SDK!\nCreated at: " . date('Y-m-d H:i:s');
    $file = $client->files->upload($folderPath . '/test.txt', $content);
    echo "   Uploaded: {$file['path_display']}\n";
    echo "   Size: {$file['size']} bytes\n\n";

    echo "5. Listing folder contents...\n";
    $contents = $client->files->listFolder($folderPath);
    foreach ($contents['entries'] as $entry) {
        echo "   - {$entry['name']} ({$entry['.tag']})\n";
    }
    echo "\n";

    echo "6. Downloading the file...\n";
    $downloaded = $client->files->download($folderPath . '/test.txt');
    echo "   Content: " . substr($downloaded['content'], 0, 50) . "...\n\n";

    echo "7. Creating a shared link...\n";
    $link = $client->sharing->createSharedLinkWithSettings($folderPath . '/test.txt');
    echo "   Share URL: {$link['url']}\n\n";

    echo "8. Searching for files...\n";
    $results = $client->files->search('test', $folderPath, 10);
    echo "   Found {$results['matches'][0]['metadata']['metadata']['name']}\n\n";

    echo "9. Cleaning up (deleting test folder)...\n";
    $client->files->delete($folderPath);
    echo "   Deleted: $folderPath\n\n";

    echo "✓ All operations completed successfully!\n";

} catch (DropboxException $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    echo "  Status Code: {$e->getCode()}\n";
    
    if ($e->getErrorSummary()) {
        echo "  Error Summary: {$e->getErrorSummary()}\n";
    }
    
    exit(1);
}
