<?php
# logout.php - logs the user out and destroys their session
# Zakir Mohammed - 250204760
# DG1IAD Internet Applications and Databases
require_once 'config.php';

// clear all session data
$_SESSION = [];

// delete the session cookie so it cant be reused
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// destroy the session on the server
session_destroy();

// start a new session just to set the flash message
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Strict']);
setFlash('success', 'You have been logged out. See you next time!');

redirect('index.php');
?>
