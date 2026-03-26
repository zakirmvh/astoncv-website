<?php
# config.php - main configuration file for AstonCV
# Zakir Mohammed - 250204760
# This file handles database connection, sessions, security functions and helpers

// Database credentials for Aston University server
// These are different from local XAMPP settings
define('DB_HOST', 'localhost');            // database server host
define('DB_NAME', 'dg250204760_db');       // assigned university database
define('DB_USER', 'dg250204760');          // MySQL username
define('DB_PASS', 'F8OInXwQFi430eGXdvqlCZQGz');  // MySQL password

// Function to connect to the database using PDO
// PDO is used because it supports prepared statements which prevent SQL injection
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // show database errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // return results as associative arrays
                PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements for security
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        // Do not show actual database error to the user for security reasons
        die("Database connection failed. Please try again later.");
    }
}

// Start secure session
// HttpOnly prevents JavaScript from accessing session cookies (helps prevent XSS)
// SameSite helps prevent CSRF attacks
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
]);

// Generate CSRF token for form protection
// A random token is stored in the session and checked when forms are submitted
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // create secure random token
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token when form is submitted
// hash_equals prevents timing attacks
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Escape output to prevent XSS attacks
// Converts special characters like < > into safe HTML entities
function esc($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect helper function
function redirect($url) {
    header("Location: $url");
    exit;
}

// Flash messages are temporary messages shown after redirect
// Example: "Login successful" or "CV updated"
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Get flash message once then delete it
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Get initials from user's name for avatar display
// Example: "Zakir Mohammed" -> "ZM"
function getInitials($name) {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return $initials;
}

// Check URL starts with http or https only
// Prevents dangerous links like javascript: or data:
function isSafeURL($url) {
    $url = trim($url);
    return preg_match('/^https?:\/\//i', $url);
}
?>

