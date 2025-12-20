<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dropbox Access Token
    |--------------------------------------------------------------------------
    |
    | Your Dropbox OAuth access token. You can generate one from the
    | Dropbox App Console or use the OAuth flow to obtain it.
    |
    | @link https://www.dropbox.com/developers/apps
    |
    */

    'access_token' => env('DROPBOX_ACCESS_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Dropbox App Credentials
    |--------------------------------------------------------------------------
    |
    | Your Dropbox app credentials for OAuth authentication.
    | These are used for the OAuth flow to obtain access tokens.
    |
    */

    'app_key' => env('DROPBOX_APP_KEY', ''),
    'app_secret' => env('DROPBOX_APP_SECRET', ''),
    'redirect_uri' => env('DROPBOX_REDIRECT_URI', ''),

];
