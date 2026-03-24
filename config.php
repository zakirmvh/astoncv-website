<?php
/**
 * config.php — Core configuration for AstonCV
 * 
 * Handles database connection, session management, CSRF protection,
 * output escaping, flash messages, and helper functions.
 * 
 * DEPLOYMENT NOTE:
 * Update DB_HOST, DB_NAME, DB_USER, and DB_PASS below to match
 * your host server credentials before deploying. On the Aston
 * server, your database name is typically your student number
 * (e.g. '250204760') or a name assigned by the admin.
 * 
 * @author  Zakir Mohammed
 * @student 250204760
 * @email   250204760@aston.ac.uk
 * @module  DG1IAD Internet Applications and Databases
 * @course  BSc Computer Science (Year 1), Aston University
 * @date    March 2026
 */

// ─── Database Credentials ───────────────────────────────────────
// LOCAL (XAMPP):  host=127.0.0.1, user=root, pass=''
// ASTON SERVER:   update these values to match your server setup
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'astoncv');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Create and return a PDO database connection.
 * Uses prepared statements (emulate off) to prevent SQL injection.
 * 
 * @return PDO The database connection object
 */
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                // Throw exceptions on errors for easier debugging
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // Return results as associative arrays
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Disable emulated prepares for true parameterised queries
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        // Do not expose internal error details to the user
        die("Database connection failed. Please try again later.");
    }
}

// ─── Session Configuration ──────────────────────────────────────
// httponly: prevents JavaScript from accessing session cookie (XSS protection)
// samesite: prevents CSRF by restricting cross-site cookie sending
// strict_mode: rejects uninitialized session IDs
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
]);

// ─── CSRF Protection ────────────────────────────────────────────

/**
 * Generate a CSRF token for form protection.
 * Creates one token per session and reuses it.
 * 
 * @return string The CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a submitted CSRF token against the session token.
 * Uses hash_equals() to prevent timing attacks.
 * 
 * @param string $token The token from the form submission
 * @return bool True if the token is valid
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ─── Output Escaping ────────────────────────────────────────────

/**
 * Escape a string for safe HTML output (prevents XSS).
 * Converts special characters like <, >, &, ", ' to HTML entities.
 * 
 * @param string|null $string The input string
 * @return string The escaped string
 */
function esc($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// ─── Authentication Helpers ─────────────────────────────────────

/**
 * Check if the current user is logged in.
 * 
 * @return bool True if user_id exists in the session
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect the user to another page and stop execution.
 * 
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

// ─── Flash Messages ─────────────────────────────────────────────
// Flash messages persist across one redirect, then disappear.

/**
 * Set a flash message to display after a redirect.
 * 
 * @param string $type    'success' or 'error'
 * @param string $message The message text
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieve and clear the flash message (if any).
 * 
 * @return array|null The flash message array or null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ─── Utility Helpers ────────────────────────────────────────────

/**
 * Get the initials from a person's name for the avatar display.
 * E.g. "Zakir Mohammed" returns "ZM".
 * 
 * @param string $name The full name
 * @return string 1-2 uppercase initials
 */
function getInitials($name) {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return $initials;
}

/**
 * Validate that a URL begins with http:// or https://.
 * Used to prevent javascript: or data: URI injection in links.
 * 
 * @param string $url The URL to check
 * @return bool True if the URL is safe to render as a link
 */
function isSafeURL($url) {
    $url = trim($url);
    return preg_match('/^https?:\/\//i', $url);
}
?>
