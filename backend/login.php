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
 * }
 *
*/

session_start();
require_once "admin.php";

header('Content-Type: application/json');

$username = null;
$password = null;
if (isset($_POST['username'])) $username = $_POST['username'];
if (isset($_POST['password'])) $password = $_POST['password'];

$authenticated = check_login($username, $password);
if ($authenticated == true) {
    echo json_encode(['auth' => true, 'username' => $_POST['username']]);
    return;
}
echo json_encode(['auth' => false]);
