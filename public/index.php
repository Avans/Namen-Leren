<?php

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Avans.php';

$secrets = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sso.php';

$server = new  Avans\NamenLeren\Avans([
    'identifier' => $secrets['key'],
    'secret' => $secrets['secret'],
    'callback_uri' => $secrets['redirect_uri']
]);

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

?>
<!DOCTYPE html>
<html>
<head>
    <title>Namen Leren</title>
    <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>

    <script src="namenleren.js"></script>
    <style>
        body {
            text-align: center;
            margin-bottom: 50px;
        }

        #foto {
            width: 40%;
            margin: 10pt;
            position: relative;
            font-size: 100px;
            font-weight: bold;
            margin-left: auto;
            margin-right: auto;
        }

        #foto img {
            width: 100%;
        }

        #verberger {
            top: 0;
            left: 0;
            position: absolute;
            height: 100%;
            width: 100%;
        }

        #naam {
            font-size: 2.0em;
            padding: 0.2em;
            width: 50%;
            text-align: center;
        }

        #correct {
            color: green;
            display: none;
        }

        #helaas {
            color: red;
            display: none;
        }

        #groepen {
            width: 150px;
            position: fixed;
            top: 0;
            right: 0;
            text-align: right;
            padding: 10px;
            background-color: #fffec1;
        }

        #helper {
            position: absolute;
            top: -130px;
            left: 0;
            width: 130px;

            transition: 0.2s ease-in-out;
            z-index: 200;
        }

        #helper:hover {
            transform: scale(2);
        }

    </style>
</head>
<body>
<div id="groepen">
    Iedereen <input type="checkbox" id="all" checked="checked"><br />
</div>
<div id="foto">
    <img>
    <svg id="verberger">
        <polygon id="vierkant" points="200,10 250,190 160,210" style="fill:white" />
    </svg>
</div>

<input type="text" id="naam" autofocus><span style="position: relative;"><img id="helper"></span>


<h1 id="correct">Correct!</h1>
<h1 id="helaas">Helaas!</h1>

</body>
</html>
<?php


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
