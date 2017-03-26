<?php
/*
 * Takes the following post args:
 * token: if the token is valid with regards to a username then will return true
 * username: the username to check login status
 * password: the password to check the login status, not needed if sending what is thought to be a valid token
 *
 * This page has the following return structure. On failed login:
 * {
 *      "auth": false
 * }
 *
 * And on a successful login:
 *
 * {
 *      "auth": true,
 *      "username": "USERNAME_AS_STRING",
 *      "token": "TOKEN_AS_A_STRING"
 * }
 *
*/

require_once "admin.php";

header('Content-Type: application/json');

$username = null;
$password = null;
$token_post = null;
if (isset($_POST['username'])) $username = $_POST['username'];
if (isset($_POST['password'])) $password = $_POST['password'];
if (isset($_POST['token'])) $token_post = $_POST['token'];

$token = login_check($username, $password, $token_post);
if (isset($token)) {
    echo json_encode(['auth' => true, 'username' => $_POST['username'], 'token' => $token]);
    return;
}
echo json_encode(['auth' => false]);
