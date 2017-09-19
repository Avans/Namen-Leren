<?php

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Avans.php';

$server = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sso.php';

session_start();

// Step 4
if (array_key_exists('token_credentials', $_SESSION)) {
    // Check somebody hasn't manually entered this URL in,
    // by checking that we have the token credentials in
    // the session.
    if ( ! isset($_SESSION['token_credentials'])) {
        echo 'No token credentials.';
        exit(1);
    }
    // Retrieve our token credentials. From here, it's play time!
    $tokenCredentials = unserialize($_SESSION['token_credentials']);
    // // Below is an example of retrieving the identifier & secret
    // // (formally known as access token key & secret in earlier
    // // OAuth 1.0 specs).
    // $identifier = $tokenCredentials->getIdentifier();
    // $secret = $tokenCredentials->getSecret();
    // Some OAuth clients try to act as an API wrapper for
    // the server and it's API. We don't. This is what you
    // get - the ability to access basic information. If
    // you want to get fancy, you should be grabbing a
    // package for interacting with the APIs, by using
    // the identifier & secret that this package was
    // designed to retrieve for you. But, for fun,
    // here's basic user information.
    $user = $server->getUserDetails($tokenCredentials);

    $protected_location = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'protected';
    if ($_GET['_p'] === '') {
        include $protected_location . DIRECTORY_SEPARATOR . 'index.html';
    } else {
        $path = str_replace(['../','./'], '', $_GET['_p']);
        $filename = $protected_location . DIRECTORY_SEPARATOR . $path;
        if (!file_exists($filename)) {
            http_response_code(404);
            exit('File not found');
        }
        header('Content-Type: ' . mime_content_type($filename));
        include $filename;
    }


// Step 3
} elseif (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) {
    // Retrieve the temporary credentials from step 2
    $temporaryCredentials = unserialize($_SESSION['temporary_credentials']);
    // Third and final part to OAuth 1.0 authentication is to retrieve token
    // credentials (formally known as access tokens in earlier OAuth 1.0
    // specs).
    $tokenCredentials = $server->getTokenCredentials($temporaryCredentials, $_GET['oauth_token'], $_GET['oauth_verifier']);
    // Now, we'll store the token credentials and discard the temporary
    // ones - they're irrelevant at this stage.
    unset($_SESSION['temporary_credentials']);
    $_SESSION['token_credentials'] = serialize($tokenCredentials);
    session_write_close();
    // Redirect to the user page
    header("Location: http://{$_SERVER['HTTP_HOST']}/");
    exit;
// Step 2.5 - denied request to authorize client
} elseif (isset($_GET['denied'])) {
    echo 'Hey! You denied the client access to your Avans account! If you did this by mistake, you should <a href="/">try again</a>.';
// Step 2
} else {
    // First part of OAuth 1.0 authentication is retrieving temporary credentials.
    // These identify you as a client to the server.
    $temporaryCredentials = $server->getTemporaryCredentials();
    // Store the credentials in the session.
    $_SESSION['temporary_credentials'] = serialize($temporaryCredentials);
    session_write_close();
    // Second part of OAuth 1.0 authentication is to redirect the
    // resource owner to the login screen on the server.
    $server->authorize($temporaryCredentials);
}
