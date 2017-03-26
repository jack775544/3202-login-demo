<?php
/*
 * Takes 2 post args
 * username: The username for the user that is being created
 * password: The password for the user that is being created
 *
 * This page has the following return structure. On failed user creation:
 * {
 *      "success": false
 * }
 *
 * And on success:
 * {
 *      "success": true
 * }
 */

header('Content-Type: application/json');

require_once "admin.php";

// Make a database connection
$database_path = $_SERVER['DOCUMENT_ROOT'] . '/backend/database.db';
$database_file = 'sqlite:' . $database_path;
$db = new PDO($database_file) or die("Cannot open database");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$username = $_POST['username'];

// See if there is a duplicate username
$query = $db->prepare("SELECT count(*) FROM USERS WHERE username = ?");
$query->execute(array($username));
$count = $query->fetch(PDO::FETCH_NUM);
$user_count = $count[0];

$response = array();

// The username does not exist, so create user
if ($user_count < 1) {
    $query = $db->prepare("INSERT INTO USERS VALUES (?, ?, ?)");
    $query->execute(array($username, encrypt_password($_POST['password']), md5(uniqid(mt_rand(), true))));
    // User has been made, return success
    $response['success'] = true;
    echo json_encode($response);
    return;
}

$response['success'] = false;
echo json_encode($response);