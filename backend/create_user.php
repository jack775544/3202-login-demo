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

$success = false;
if (isset($_POST['username']) && isset($_POST['password'])) {
    $success = create_user($_POST['username'], $_POST['password']);
}

echo json_encode(["success" => $success]);
