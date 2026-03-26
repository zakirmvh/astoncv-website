<?php
# header.php - shared nav bar for all pages
# Zakir Mohammed - 250204760
# DG1IAD
require_once 'config.php';

// work out which page we're on so we can highlight the right nav link
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

<!-- nav bar -->
<nav>
    <a href="index.php" class="brand">
        <span class="brand-dot"></span>
        astoncv
    </a>
    <ul class="nav-links">
        <?php if (isLoggedIn()): ?>
            <!-- logged in users see these links -->
            <li><a href="index.php" <?= $currentPage === 'index.php' ? 'class="active"' : '' ?>>Explore</a></li>
            <li><a href="search.php" <?= $currentPage === 'search.php' ? 'class="active"' : '' ?>>Search</a></li>
            <li><a href="edit.php" <?= $currentPage === 'edit.php' ? 'class="active"' : '' ?>>My CV</a></li>
            <li><a href="password.php" <?= $currentPage === 'password.php' ? 'class="active"' : '' ?>>Password</a></li>
            <li><a href="logout.php" class="accent"><?= esc($_SESSION['user_name']) ?> &rarr;</a></li>
        <?php else: ?>
            <!-- not logged in users see these links -->
            <li><a href="index.php" <?= $currentPage === 'index.php' ? 'class="active"' : '' ?>>Explore</a></li>
            <li><a href="search.php" <?= $currentPage === 'search.php' ? 'class="active"' : '' ?>>Search</a></li>
            <li><a href="login.php" <?= $currentPage === 'login.php' ? 'class="active"' : '' ?>>Login</a></li>
            <li><a href="register.php" class="<?= $currentPage === 'register.php' ? 'active' : 'accent' ?>">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>

<div class="container">

<?php
// show flash message if there is one (like "logged out" or "cv saved")
$flash = getFlash();
if ($flash): ?>
    <div class="alert alert-<?= esc($flash['type']) ?>">
        <?= esc($flash['message']) ?>
    </div>
<?php endif; ?>
