<?php
/*
 * This function can be used to generate the URL an account owner would use to allow your app to access their account.
 * After visiting the URL, the account owner is prompted to log in and allow your app to access their account.
 * They are then redirected to your redirect URL with the authorization code appended as a query parameter. e.g.:
 * http://localhost:8888/?code={authorization_code}
 */

/***
 * @param $redirectURI - URL Encoded Redirect URI
 * @param $clientId - API Key
 * @return string - Full Authorization URL
 */

$redirectURI = 'http://104.248.8.25/ccnewsletter/response.php';
$clientId = '441f4aef-c736-41c9-a8f2-59bb1c138366';
$clientSecret = 'JXldK2nHIKCmlK9KZpVIgw';

function getAuthorizationURL($redirectURI, $clientId) {
    // Create authorization URL
    $baseURL = "https://api.cc.email/v3/idfed";
    $authURL = $baseURL . "?client_id=" . $clientId . "&scope=contact_data&response_type=code" . "&redirect_uri=" . $redirectURI;

    return $authURL;

}

$newURL = getAuthorizationURL($redirectURI, $clientId);
header('Location: '.$newURL);
die();
?>