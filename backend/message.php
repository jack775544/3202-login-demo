<?php
/*
 * Returns a message to a validated user
 * Takes the following post args:
 * username: the username of the user to get a message for
 * token: auth token for the user
 *
 * And returns a message as text/plain
 */
require_once "admin.php";

header("Content-Type: text/plain");

$username = null;
$token_post = null;
if (isset($_POST['username'])) $username = $_POST['username'];
if (isset($_POST['token'])) $token_post = $_POST['token'];

$token = login_check($username, null, $token_post);
if (isset($token)) {
    echo "Hello " . $_POST['username'] . ", you are logged into the system";
}