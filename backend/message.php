<?php
/*
 * Returns a message to a validated user
 *
 * And returns a message as text/plain
 */
session_start();
require_once "admin.php";

header("Content-Type: text/plain");

$authenticated = check_login();
if ($authenticated == true) {
    echo "Hello " . $_SESSION['username'] . ", you are logged into the system";
}
