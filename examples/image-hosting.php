<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\Dropbox\DropboxClient;

$accessToken = getenv('DROPBOX_ACCESS_TOKEN') ?: 'your_access_token_here';
$client = new DropboxClient($accessToken);

echo "=== Dropbox Image Hosting Examples ===\n\n";

// Example 1: Convert existing shared link to direct link
echo "1. Converting existing shared link:\n";
$sharedLink = 'https://www.dropbox.com/s/abcd1234/vacation.jpg?dl=0';

// Method 1: Using userusercontent (recommended)
$directLink1 = $client->sharing->convertToDirectLink($sharedLink, 'userusercontent');
echo "   Original: $sharedLink\n";
echo "   Direct (userusercontent): $directLink1\n\n";

// Method 2: Using raw parameter
$directLink2 = $client->sharing->convertToDirectLink($sharedLink, 'raw');
echo "   Direct (raw): $directLink2\n\n";

// Example 2: Create a new shared link and convert it immediately
echo "2. Creating and converting a new shared link:\n";
try {
    $result = $client->sharing->createDirectLink('/Photos/example.jpg');
    
    echo "   File: {$result['name']}\n";
    echo "   Original URL: {$result['url']}\n";
    echo "   Direct URL: {$result['direct_url']}\n\n";
    
    // Use in HTML
    echo "   HTML usage:\n";
    echo "   <img src=\"{$result['direct_url']}\" alt=\"{$result['name']}\">\n\n";
    
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Batch conversion of multiple links
echo "3. Converting multiple links:\n";
$links = [
    'https://www.dropbox.com/s/abc123/image1.jpg?dl=0',
    'https://www.dropbox.com/s/def456/image2.png?dl=0',
    'https://www.dropbox.com/s/ghi789/image3.gif?dl=0',
];

foreach ($links as $link) {
    $directLink = $client->sharing->convertToDirectLink($link);
    echo "   " . basename(parse_url($link, PHP_URL_PATH)) . " -> $directLink\n";
}
echo "\n";

// Example 4: Create image gallery with direct links
echo "4. Creating image gallery:\n";
try {
    $photos = $client->files->listFolder('/Photos');
    $gallery = [];
    
    foreach ($photos['entries'] as $photo) {
        if ($photo['.tag'] === 'file' && preg_match('/\.(jpg|jpeg|png|gif)$/i', $photo['name'])) {
            try {
                // Create shared link if doesn't exist
                $linkData = $client->sharing->createDirectLink($photo['path_display']);
                $gallery[] = [
                    'name' => $photo['name'],
                    'url' => $linkData['direct_url'],
                    'size' => $photo['size'],
                ];
            } catch (Exception $e) {
                // Link might already exist, try to get existing one
                $links = $client->sharing->listSharedLinks($photo['path_display']);
                if (!empty($links['links'])) {
                    $directUrl = $client->sharing->convertToDirectLink($links['links'][0]['url']);
                    $gallery[] = [
                        'name' => $photo['name'],
                        'url' => $directUrl,
                        'size' => $photo['size'],
                    ];
                }
            }
        }
    }
    
    echo "   Found " . count($gallery) . " images:\n";
    foreach ($gallery as $image) {
        echo "   - {$image['name']} (" . number_format($image['size'] / 1024, 2) . " KB)\n";
        echo "     URL: {$image['url']}\n";
    }
    
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Example 5: Generate HTML gallery
echo "5. HTML Gallery Example:\n";
echo "   <div class=\"gallery\">\n";
if (!empty($gallery)) {
    foreach (array_slice($gallery, 0, 3) as $image) {
        echo "       <img src=\"{$image['url']}\" alt=\"{$image['name']}\" />\n";
    }
}
echo "   </div>\n\n";

// Example 6: Markdown usage
echo "6. Markdown Usage:\n";
if (!empty($gallery)) {
    foreach (array_slice($gallery, 0, 2) as $image) {
        echo "   ![{$image['name']}]({$image['url']})\n";
    }
}
echo "\n";

// Example 7: Using with settings (password protected, expiration)
echo "7. Creating password-protected direct link:\n";
try {
    $result = $client->sharing->createDirectLink(
        '/Documents/secret-image.jpg',
        [
            'link_password' => 'mypassword123',
            'expires' => date('Y-m-d\TH:i:s\Z', strtotime('+7 days')),
        ]
    );
    
    echo "   Direct URL: {$result['direct_url']}\n";
    echo "   Expires: " . date('Y-m-d H:i:s', strtotime('+7 days')) . "\n";
    echo "   Note: Password protection applies to the link\n\n";
    
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n\n";
}

echo "=== Complete! ===\n";
