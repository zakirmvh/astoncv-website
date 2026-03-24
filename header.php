<?php
/**
 * header.php — Shared page header and navigation
 * 
 * @author  Zakir Mohammed
 * @student 250204760
 * @module  DG1IAD Internet Applications and Databases
 * 
 * Included at the top of every page to provide consistent
 * navigation, page title, and CSS. Also displays flash messages.
 */
require_once 'config.php';

// Determine current page for active nav highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'AstonCV') ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation bar with pill-shaped links -->
<nav>
    <a href="index.php" class="brand">
        <span class="brand-dot"></span>
        astoncv
    </a>
    <ul class="nav-links">
        <?php if (isLoggedIn()): ?>
            <li><a href="index.php" <?= $currentPage === 'index.php' ? 'class="active"' : '' ?>>Explore</a></li>
            <li><a href="search.php" <?= $currentPage === 'search.php' ? 'class="active"' : '' ?>>Search</a></li>
            <li><a href="edit.php" <?= $currentPage === 'edit.php' ? 'class="active"' : '' ?>>My CV</a></li>
            <li><a href="password.php" <?= $currentPage === 'password.php' ? 'class="active"' : '' ?>>Password</a></li>
            <li><a href="logout.php" class="accent"><?= esc($_SESSION['user_name']) ?> &rarr;</a></li>
        <?php else: ?>
            <li><a href="index.php" <?= $currentPage === 'index.php' ? 'class="active"' : '' ?>>Explore</a></li>
            <li><a href="search.php" <?= $currentPage === 'search.php' ? 'class="active"' : '' ?>>Search</a></li>
            <li><a href="login.php" <?= $currentPage === 'login.php' ? 'class="active"' : '' ?>>Login</a></li>
            <li><a href="register.php" class="<?= $currentPage === 'register.php' ? 'active' : 'accent' ?>">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>

<div class="container">

<?php
// Display flash message if one exists (persists across redirects)
$flash = getFlash();
if ($flash): ?>
    <div class="alert alert-<?= esc($flash['type']) ?>">
        <?= esc($flash['message']) ?>
    </div>
<?php endif; ?>
