<?php

// oauth2.php
use Google\Client;
use Google\Service\Sheets;

require 'vendor/autoload.php';
require 'config.php';

$client = new Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope([
    Sheets::SPREADSHEETS,
    Google\Service\Drive::DRIVE_FILE
]);
$client->setAccessType('offline'); // For refresh tokens

if (!isset($_GET['code'])) {
    // Step 1: Get authorization code
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit();
} else {
    // Step 2: Exchange authorization code for access token
    $client->fetchAccessTokenWithAuthCode($_GET['code']);
    // Save the access token for future use
    file_put_contents('token.json', json_encode($client->getAccessToken()));
    echo 'Access token saved to token.json';
}
