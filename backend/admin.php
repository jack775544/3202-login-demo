<?php

function connect_database(){
    $database_path = $_SERVER['DOCUMENT_ROOT'] . '/backend/database.db';
    $database_file = 'sqlite:' . $database_path;
    $db = new PDO($database_file) or die("Cannot open database");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

/**
 * Encrypts a password into a strong hash
 * Logic taken from https://alias.io/2010/01/store-passwords-safely-with-php-and-mysql/
 * @param $password string The password to encrypt
 * @param $cost int The cost of the encryption, higher cost is more processing time but stronger hash
 * @return string A hash of the password
 */
function encrypt_password($password, $cost = 10)
{
    // Create a random salt
    $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

    // Prefix information about the hash so PHP knows how to verify it later.
    // "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
    $salt = sprintf("$2a$%02d$", $cost) . $salt;
    return crypt($password, $salt);
}

/**
 * Checks a hash with a password from the database
 * @param $password string The password to check
 * @param $hash string The password from the database
 * @return bool true if matching, false if not
 */
function check_password($password, $hash)
{
    return hash_equals($hash, crypt($password, $hash));
}

/**
 * Checks to see if a user can log in and if so return their token.
 * Note if no password is present, but the token is then the method can still perform auth
 * Conversely if no token is present and the password is then the method can still perform auth
 * @param $username string The username of the user to check
 * @param $password string The password of the user to check
 * @param $token string The login token of the user to check
 * @return string The user token as a string on success or null on fail
 */
function check_login($username, $password, $token)
{
    // Set up database
    $db = connect_database();

    // Check if their current token is valid
    if (isset($token) && isset($username)) {
        $query = $db->prepare("SELECT count(*) FROM USERS WHERE username = ? AND token = ?");
        $query->execute(array($username, $token));

        $count = $query->fetch(PDO::FETCH_NUM);
        $user_count = $count[0];
        // They have given us the correct token and username, so return the token they have given us
        if ($user_count == 1) {
            return $token;
        }
    }

    // Password auth next
    if (isset($username) && isset($password)) {
        $query = $db->prepare("SELECT username, password, token FROM USERS WHERE username = ?");
        $query->execute(array($username));
        $user = $query->fetch(PDO::FETCH_NUM);
        // If we actually got a user back
        if ($user != false) {
            $password_correct = check_password($password, $user[1]);
            if ($password_correct == true) {
                return $user[2];
            }
        }
    }

    // Their credentials don't match, so return a failure
    return null;
}

function create_user($username, $password) {
    $db = connect_database();

    // See if there is a duplicate username
    $query = $db->prepare("SELECT count(*) FROM USERS WHERE username = ?");
    $query->execute(array($username));
    $count = $query->fetch(PDO::FETCH_NUM);
    $user_count = $count[0];

    // The username does not exist, so create user
    if ($user_count < 1) {
        $query = $db->prepare("INSERT INTO USERS VALUES (?, ?, ?)");
        $query->execute(array($username, encrypt_password($password), md5(uniqid(mt_rand(), true))));
        // User has been made, return success
        return true;
    }

    return false;
}