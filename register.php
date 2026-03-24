<?php
/**
 * register.php — User Registration
 * 
 * @author  Zakir Mohammed
 * @student 250204760
 * @module  DG1IAD Internet Applications and Databases
 * 
 * Allows new users to create an account with name, email, and password.
 * Includes both server-side and client-side form validation.
 * 
 * Security features:
 * - CSRF token validation
 * - Password hashed with bcrypt before storage
 * - Email uniqueness check
 * - Password strength: min 8 chars, at least 1 letter and 1 number
 * - Session ID regenerated after login (prevents session fixation)
 * - All input trimmed and validated server-side
 */
$pageTitle = 'AstonCV — Register';
require_once 'header.php';

// Redirect if already logged in
if (isLoggedIn()) { redirect('edit.php'); }

$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    // Trim and collect input
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ─── Server-side Validation ─────────────────────────────
    if (empty($name) || strlen($name) > 100) {
        $errors[] = 'Name is required (max 100 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errors[] = 'A valid email address is required.';
    }
    // Stronger password: min 8 chars, must contain letter and number
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one letter and one number.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $db = getDB();

        // Check if email is already registered
        $stmt = $db->prepare("SELECT id FROM cvs WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            // Hash the password with bcrypt
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert new user
            $stmt = $db->prepare("INSERT INTO cvs (name, email, password) VALUES (:name, :email, :password)");
            $stmt->execute([':name' => $name, ':email' => $email, ':password' => $hashedPassword]);

            // Auto-login after registration
            $_SESSION['user_id']   = $db->lastInsertId();
            $_SESSION['user_name'] = $name;
            // Regenerate session ID to prevent session fixation attack
            session_regenerate_id(true);

            setFlash('success', 'Registration successful! Complete your CV below.');
            redirect('edit.php');
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<div class="card" style="max-width: 460px; margin: 20px auto;">
    <h2>Create account</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e): ?>
                <div><?= esc($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php" id="registerForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= esc($csrfToken) ?>">

        <div class="form-group">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" value="<?= esc($name) ?>"
                   required maxlength="100" autocomplete="name" placeholder="Zakir Mohammed">
        </div>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?= esc($email) ?>"
                   required maxlength="100" autocomplete="email" placeholder="you@aston.ac.uk">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   required minlength="8" autocomplete="new-password">
            <div class="form-help">Min 8 characters, must include a letter and a number.</div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   required minlength="8" autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;">Register</button>
    </form>

    <p style="margin-top: 16px; text-align: center; font-size: 13px; color: var(--text-dim);">
        Already have an account? <a href="login.php">Login</a>
    </p>
</div>

<!-- Client-side form validation -->
<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    var name = document.getElementById('name').value.trim();
    var email = document.getElementById('email').value.trim();
    var pass = document.getElementById('password').value;
    var confirm = document.getElementById('confirm_password').value;
    var errors = [];

    // Validate name
    if (name.length === 0) errors.push('Name is required.');
    if (name.length > 100) errors.push('Name must be under 100 characters.');

    // Validate email format
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Please enter a valid email.');

    // Validate password strength
    if (pass.length < 8) errors.push('Password must be at least 8 characters.');
    if (!/[A-Za-z]/.test(pass) || !/[0-9]/.test(pass)) errors.push('Password needs a letter and a number.');

    // Validate password match
    if (pass !== confirm) errors.push('Passwords do not match.');

    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
    }
});
</script>

<?php require_once 'footer.php'; ?>
