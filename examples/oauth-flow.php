<?php

/**
 * OAuth 2.0 Flow Example
 * 
 * This example demonstrates how to implement the OAuth flow
 * to obtain access tokens from Dropbox.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\Dropbox\DropboxClient;

// Configuration
$appKey = getenv('DROPBOX_APP_KEY') ?: 'your_app_key';
$appSecret = getenv('DROPBOX_APP_SECRET') ?: 'your_app_secret';
$redirectUri = 'http://localhost:8000/callback.php';

session_start();

// Step 1: Generate authorization URL and redirect user
if (!isset($_GET['code'])) {
    // Generate random state for CSRF protection
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;

    // Generate authorization URL
    $authUrl = DropboxClient::getAuthorizationUrl(
        $appKey,
        $redirectUri,
        $state,
        ['files.content.write', 'files.content.read', 'sharing.write']
    );

    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Dropbox OAuth Example</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .button { display: inline-block; padding: 12px 24px; background: #0061ff; color: white; 
                     text-decoration: none; border-radius: 4px; font-weight: bold; }
            .button:hover { background: #0052cc; }
        </style>
    </head>
    <body>
        <h1>Dropbox OAuth Example</h1>
        <p>Click the button below to authorize this application to access your Dropbox account.</p>
        <p><a href=\"$authUrl\" class=\"button\">Connect to Dropbox</a></p>
        <h2>Configuration</h2>
        <ul>
            <li><strong>App Key:</strong> $appKey</li>
            <li><strong>Redirect URI:</strong> $redirectUri</li>
        </ul>
        <p><small>Make sure the redirect URI is configured in your Dropbox app settings.</small></p>
    </body>
    </html>";
    exit;
}

// Step 2: Handle callback and exchange code for token
try {
    // Verify state parameter (CSRF protection)
    if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
        throw new Exception('Invalid state parameter. Possible CSRF attack.');
    }

    // Exchange authorization code for access token
    $tokenData = DropboxClient::getAccessToken(
        $_GET['code'],
        $appKey,
        $appSecret,
        $redirectUri
    );

    // Store tokens securely (in production, use database)
    $_SESSION['dropbox_access_token'] = $tokenData['access_token'];
    $_SESSION['dropbox_refresh_token'] = $tokenData['refresh_token'] ?? null;
    $_SESSION['dropbox_token_type'] = $tokenData['token_type'];
    $_SESSION['dropbox_expires_in'] = $tokenData['expires_in'] ?? null;

    // Create Dropbox client with new token
    $client = new DropboxClient($tokenData['access_token']);

    // Get account information
    $account = $client->users->getCurrentAccount();
    $space = $client->users->getSpaceUsage();

    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>OAuth Success</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; 
                      padding: 12px; border-radius: 4px; margin-bottom: 20px; }
            .info-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; 
                       border-radius: 4px; margin: 10px 0; }
            code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class='success'>
            <strong>✓ Success!</strong> Your Dropbox account has been connected.
        </div>
        
        <h2>Account Information</h2>
        <div class='info-box'>
            <p><strong>Name:</strong> {$account['name']['display_name']}</p>
            <p><strong>Email:</strong> {$account['email']}</p>
            <p><strong>Account ID:</strong> {$account['account_id']}</p>
            <p><strong>Country:</strong> {$account['country']}</p>
        </div>

        <h2>Space Usage</h2>
        <div class='info-box'>
            <p><strong>Used:</strong> " . number_format($space['used'] / 1024 / 1024, 2) . " MB</p>
            <p><strong>Allocated:</strong> " . number_format($space['allocation']['allocated'] / 1024 / 1024, 2) . " MB</p>
            <p><strong>Usage:</strong> " . number_format(($space['used'] / $space['allocation']['allocated']) * 100, 2) . "%</p>
        </div>

        <h2>Access Token</h2>
        <div class='info-box'>
            <p><strong>Token Type:</strong> {$tokenData['token_type']}</p>
            <p><strong>Access Token:</strong> <code>" . substr($tokenData['access_token'], 0, 20) . "...</code></p>
            " . (isset($tokenData['refresh_token']) ? "<p><strong>Refresh Token:</strong> <code>" . substr($tokenData['refresh_token'], 0, 20) . "...</code></p>" : "") . "
            " . (isset($tokenData['expires_in']) ? "<p><strong>Expires In:</strong> {$tokenData['expires_in']} seconds</p>" : "") . "
        </div>

        <p><strong>Note:</strong> In production, store these tokens securely in a database, not in session.</p>
        
        <h2>Next Steps</h2>
        <p>You can now use the access token to make API calls:</p>
        <pre><code>
\$client = new DropboxClient('{$tokenData['access_token']}');
\$files = \$client->files->listFolder('/');
        </code></pre>
    </body>
    </html>";

} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>OAuth Error</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; 
                    padding: 12px; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class='error'>
            <strong>✗ Error:</strong> {$e->getMessage()}
        </div>
        <p><a href='oauth-flow.php'>Try Again</a></p>
    </body>
    </html>";
}
