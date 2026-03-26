<?php
# config.php - main config file for AstonCV
# Zakir Mohammed - 250204760
# DG1IAD Internet Applications and Databases
# handles db connection, sessions, security stuff

// database credentials - change these when deploying to the aston server
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'astoncv');
define('DB_USER', 'root');       // change for aston server
define('DB_PASS', '');           // change for aston server

// connects to the database using PDO
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // show errors properly
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // return arrays
                PDO::ATTR_EMULATE_PREPARES   => false,                   // real prepared statements - stops sql injection
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        // dont show actual error to user for security reasons
        die("Database connection failed. Please try again later.");
    }
}

# session config
# httponly stops javascript from reading the cookie which prevents XSS
# samesite stops cross site request forgery attacks
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
]);

// generates a csrf token for form protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); # random 32 byte token
    }
    return $_SESSION['csrf_token'];
}

// checks if submitted token matches the session one
// hash_equals prevents timing attacks
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// escapes html characters to prevent XSS
// turns < > & " ' into safe html entities
function esc($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// checks if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// redirect helper
function redirect($url) {
    header("Location: $url");
    exit;
}

# flash messages - shows a message once after redirect then goes away
# like "logged out successfully" or "cv updated" etc

// saves a flash message
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// gets the flash message then deletes it so it only shows once
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// gets initials from name for the avatar e.g. "Zakir Mohammed" = "ZM"
function getInitials($name) {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return $initials;
}

// checks url starts with http or https
// stops people putting dangerous javascript: or data: links
function isSafeURL($url) {
    $url = trim($url);
    return preg_match('/^https?:\/\//i', $url);
}
?>
