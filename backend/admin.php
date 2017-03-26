<?php
/**
 * Admin functionality for the server side
 * Note: session_start() MUST be called before importing this code
 */

/**
 * Makes a database connection
 * @return PDO The connection to the database
 */
function connect_database()
{
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
 * Checks to see if the user can or is logged in and if given a username and password, log them in
 * @param $username string The username of the user to check
 * @param $password string The password of the user to check
 * @return bool True iff the user is authenticated
 */
function check_login($username = null, $password = null)
{
    // Check if their current session is valid
    if (isset($_SESSION['auth']) && (!isset($username) || !isset($password))) {
        return $_SESSION['auth'] == true;
    }

    // Set up database
    $db = connect_database();

    // Password auth next
    if (isset($username) && isset($password)) {
        $query = $db->prepare("SELECT username, password FROM USERS WHERE username = ?");
        $query->execute(array($username));
        $user = $query->fetch(PDO::FETCH_NUM);
        // If we actually got a user back
        if ($user != false) {
            $password_correct = check_password($password, $user[1]);
            if ($password_correct == true) {
                $_SESSION['auth'] = true;
                $_SESSION['username'] = $username;
                return true;
            }
        }
    }

    // Their credentials don't match, so return a failure, failed auth results in potential embarrassment by peers,
    // hope of a password reset feature, and possibly drawing bad tarot cards. Also their session is no longer
    // authenticated.
    $_SESSION['auth'] = false;
    return false;
}

/**
 * Creates a user with the given username and password and inserts them into the database
 * Note that the created user is NOT authenticated
 * @param $username string The username of the user to be created
 * @param $password string The password of the user to be created
 * @return bool true iff the user was created successfully
 */
function create_user($username, $password)
{
    $db = connect_database();

    // See if there is a duplicate username
    $query = $db->prepare("SELECT count(*) FROM USERS WHERE username = ?");
    $query->execute(array($username));
    $count = $query->fetch(PDO::FETCH_NUM);
    $user_count = $count[0];

    // The username does not exist, so create user
    if ($user_count < 1) {
        $query = $db->prepare("INSERT INTO USERS VALUES (?, ?)");
        $query->execute(array($username, encrypt_password($password)));
        // User has been made, return success
        return true;
    }

    return false;
}


function logout_user(){
    try {
        session_unset();
    } catch (Exception $e) {
        return false;
    }
    return true;
}