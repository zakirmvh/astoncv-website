<?php
/**
 * password.php — Change Password
 * 
 * @author  Zakir Mohammed
 * @student 250204760
 * @module  DG1IAD Internet Applications and Databases
 * 
 * Allows authenticated users to change their password.
 * Requires the current password for verification before allowing change.
 * 
 * Security features:
 * - Authorization: must be logged in
 * - CSRF protection
 * - Current password verification before change
 * - New password must meet strength requirements
 * - New password hashed with bcrypt before storage
 */
$pageTitle = 'AstonCV — Change Password';
require_once 'header.php';

// Authorization check
if (!isLoggedIn()) { redirect('login.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($currentPassword)) {
        $errors[] = 'Current password is required.';
    }
    if (strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $errors[] = 'New password must contain at least one letter and one number.';
    }
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New passwords do not match.';
    }

    if (empty($errors)) {
        $db = getDB();

        // Fetch current password hash
        $stmt = $db->prepare("SELECT password FROM cvs WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();

        // Verify current password
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        } else {
            // Hash and update the new password
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE cvs SET password = :password WHERE id = :id");
            $stmt->execute([':password' => $newHash, ':id' => $_SESSION['user_id']]);

            setFlash('success', 'Password changed successfully.');
            redirect('password.php');
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<div class="card" style="max-width: 420px; margin: 20px auto;">
    <h2>Change password</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e): ?>
                <div><?= esc($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="password.php" id="passwordForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= esc($csrfToken) ?>">

        <div class="form-group">
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>

        <div class="form-group">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password" required minlength="8">
            <div class="form-help">Min 8 characters, must include a letter and a number.</div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm new password</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Change password</button>
    </form>
</div>

<!-- Client-side validation -->
<script>
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    var curr = document.getElementById('current_password').value;
    var pass = document.getElementById('new_password').value;
    var conf = document.getElementById('confirm_password').value;
    var errors = [];

    if (!curr) errors.push('Enter your current password.');
    if (pass.length < 8) errors.push('New password must be at least 8 characters.');
    if (!/[A-Za-z]/.test(pass) || !/[0-9]/.test(pass)) errors.push('Password needs a letter and a number.');
    if (pass !== conf) errors.push('New passwords do not match.');

    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
    }
});
</script>

<?php require_once 'footer.php'; ?>
