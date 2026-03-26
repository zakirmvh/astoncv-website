<?php
# login.php - user login page
# Zakir Mohammed - 250204760
# DG1IAD Internet Applications and Databases
#
# security: csrf token, bcrypt password_verify, generic error message
# so attackers cant tell if email exists, session regeneration

$pageTitle = 'AstonCV — Login';
require_once 'header.php';

if (isLoggedIn()) { redirect('edit.php'); }

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // check csrf token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = 'Both email and password are required.';
    }

    if (empty($errors)) {
        $db = getDB();

        // find user by email using prepared statement
        $stmt = $db->prepare("SELECT id, name, password FROM cvs WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // check password with bcrypt
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); # stop session fixation attacks
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect('edit.php');
        } else {
            // generic message - dont tell them if the email exists or not
            $errors[] = 'Invalid email or password.';
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<div class="card" style="max-width: 420px; margin: 40px auto;">
    <h2>Welcome back</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e): ?>
                <div><?= esc($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php" id="loginForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= esc($csrfToken) ?>">

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= esc($email) ?>"
                   required maxlength="100" autocomplete="email" placeholder="you@aston.ac.uk">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   required autocomplete="current-password">
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
    </form>

    <p style="margin-top: 16px; text-align: center; font-size: 13px; color: var(--text-dim);">
        No account? <a href="register.php">Register here</a>
    </p>
</div>

<!-- quick js check before submitting -->
<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    var email = document.getElementById('email').value.trim();
    var pass = document.getElementById('password').value;
    if (!email || !pass) {
        e.preventDefault();
        alert('Please enter both email and password.');
    }
});
</script>

<?php require_once 'footer.php'; ?>
