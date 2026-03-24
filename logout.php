<?php
/**
 * logout.php — User Logout
 * 
 * @author  Zakir Mohammed
 * @student 250204760
 * @module  DG1IAD Internet Applications and Databases
 * 
 * Securely destroys the user's session and redirects to the homepage.
 * Also deletes the session cookie to prevent session reuse.
 */
require_once 'config.php';

// Store name for flash message before destroying session
$name = $_SESSION['user_name'] ?? '';

// Clear all session data
$_SESSION = [];

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session on the server
session_destroy();

// Start a new session to set the flash message
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Strict']);
setFlash('success', 'You have been logged out. See you next time!');

redirect('index.php');
?>
