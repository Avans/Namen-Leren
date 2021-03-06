<?php

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/**
 * @var $server \Avans\OAuth\Web
 */
$server = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sso.php';

session_start();

if (array_key_exists('token_credentials', $_SESSION)) {
    if ( ! isset($_SESSION['token_credentials'])) {
        echo 'No token credentials.';
        exit(1);
    }
    $tokenCredentials = unserialize($_SESSION['token_credentials']);
    $protected_location = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'protected';
    if (array_key_exists('user', $_SESSION) === false) {
        $_SESSION['user'] = serialize($server->getUserDetails($tokenCredentials));
    }
    $user = unserialize($_SESSION['user']);
    if ($user->extra['employee'] !== true) {
        http_response_code(403);
        unset($_SESSION['token_credentials']);
        unset($_SESSION['user']);
        session_destroy();
        exit('Forbidden');
    }

    if ($_GET['_p'] === '') {
        include $protected_location . DIRECTORY_SEPARATOR . 'index.html';
        exit;
    } elseif ($_GET['_p'] === 'logout') {
        unset($_SESSION['token_credentials']);
        unset($_SESSION['user']);
        session_destroy();
        header("Location: /");
        exit;
    }

    $path = str_replace(['../','./'], '', $_GET['_p']);
    $filename = $protected_location . DIRECTORY_SEPARATOR . $path;
    if (!file_exists($filename)) {
        http_response_code(404);
        exit('File not found');
    }
    header('Content-Type: ' . mime_content_type($filename));
    include $filename;
    exit;

} elseif (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) {
    $temporaryCredentials = unserialize($_SESSION['temporary_credentials']);
    $tokenCredentials = $server->getTokenCredentials($temporaryCredentials, $_GET['oauth_token'], $_GET['oauth_verifier']);
    unset($_SESSION['temporary_credentials']);
    $_SESSION['token_credentials'] = serialize($tokenCredentials);
    session_write_close();
    header("Location: /");
    exit;

} elseif (isset($_GET['denied'])) {
    echo 'Hey! You denied the client access to your Avans account! If you did this by mistake, you should <a href="/">try again</a>.';
    exit;

}

$temporaryCredentials = $server->getTemporaryCredentials();
$_SESSION['temporary_credentials'] = serialize($temporaryCredentials);
session_write_close();
$server->authorize($temporaryCredentials);
