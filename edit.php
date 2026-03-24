<?php
/**
 * edit.php — Edit My CV
 * 
 * @author  Zakir Mohammed
 * @student 250204760
 * @module  DG1IAD Internet Applications and Databases
 * 
 * Allows authenticated users to update their CV details.
 * Only the logged-in user can edit their own CV (authorization).
 * 
 * Security features:
 * - Authorization check: redirects to login if not authenticated
 * - CSRF token validation on form submit
 * - Server-side validation on all inputs (length, format)
 * - URL validation: checks for http/https prefix
 * - Client-side JS validation and character counters
 */
$pageTitle = 'AstonCV — Edit My CV';
require_once 'header.php';

// Authorization: only logged-in users can access this page
if (!isLoggedIn()) { redirect('login.php'); }

$db = getDB();
$errors = [];

// Load current CV data for the logged-in user
$stmt = $db->prepare("SELECT * FROM cvs WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$cv = $stmt->fetch();

// If user record no longer exists, destroy session
if (!$cv) {
    session_destroy();
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    // Collect and trim all input
    $name           = trim($_POST['name'] ?? '');
    $keyprogramming = trim($_POST['keyprogramming'] ?? '');
    $profile        = trim($_POST['profile'] ?? '');
    $education      = trim($_POST['education'] ?? '');
    $urllinks       = trim($_POST['urllinks'] ?? '');

    // ─── Server-side Validation ─────────────────────────────
    if (empty($name) || strlen($name) > 100) {
        $errors[] = 'Name is required (max 100 characters).';
    }
    if (strlen($keyprogramming) > 255) {
        $errors[] = 'Key programming languages must be max 255 characters.';
    }
    if (strlen($profile) > 500) {
        $errors[] = 'Profile must be max 500 characters.';
    }
    if (strlen($education) > 500) {
        $errors[] = 'Education must be max 500 characters.';
    }
    if (strlen($urllinks) > 500) {
        $errors[] = 'URL links must be max 500 characters.';
    }

    // Validate URL format if provided
    if (!empty($urllinks)) {
        $urls = preg_split('/[\s,]+/', $urllinks);
        foreach ($urls as $url) {
            $url = trim($url);
            if (!empty($url) && !isSafeURL($url)) {
                $errors[] = 'URL links must begin with http:// or https://';
                break;
            }
        }
    }

    if (empty($errors)) {
        // Update CV in database using prepared statement
        $stmt = $db->prepare(
            "UPDATE cvs SET name = :name, keyprogramming = :keyprogramming, 
             profile = :profile, education = :education, URLlinks = :urllinks
             WHERE id = :id"
        );
        $stmt->execute([
            ':name'           => $name,
            ':keyprogramming' => $keyprogramming,
            ':profile'        => $profile,
            ':education'      => $education,
            ':urllinks'       => $urllinks,
            ':id'             => $_SESSION['user_id'],
        ]);

        // Update session name if changed
        $_SESSION['user_name'] = $name;

        setFlash('success', 'Your CV has been updated.');
        redirect('edit.php');
    }
}

$csrfToken = generateCSRFToken();
?>

<div class="card">
    <h2>Edit my CV</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e): ?>
                <div><?= esc($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit.php" id="editForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= esc($csrfToken) ?>">

        <!-- Name and email row -->
        <div class="form-row">
            <div class="form-group">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name"
                       value="<?= esc($cv['name']) ?>" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="email_display">Email (cannot be changed)</label>
                <input type="email" id="email_display" value="<?= esc($cv['email']) ?>" disabled>
            </div>
        </div>

        <!-- Key programming languages -->
        <div class="form-group">
            <label for="keyprogramming">Key programming languages</label>
            <input type="text" id="keyprogramming" name="keyprogramming"
                   value="<?= esc($cv['keyprogramming']) ?>" maxlength="255"
                   placeholder="e.g. Java, Python, JavaScript">
            <div class="form-help">Separate multiple languages with commas.</div>
        </div>

        <!-- Profile / summary with character counter -->
        <div class="form-group">
            <label for="profile">Profile / summary</label>
            <textarea id="profile" name="profile" maxlength="500" rows="4"
                      placeholder="A brief summary about yourself..."
                      oninput="updateCount('profile', 500)"><?= esc($cv['profile']) ?></textarea>
            <div class="char-count" id="profile-count"></div>
        </div>

        <!-- Education with character counter -->
        <div class="form-group">
            <label for="education">Education</label>
            <textarea id="education" name="education" maxlength="500" rows="3"
                      placeholder="e.g. BSc Computer Science, Aston University"
                      oninput="updateCount('education', 500)"><?= esc($cv['education']) ?></textarea>
            <div class="char-count" id="education-count"></div>
        </div>

        <!-- URL links with character counter and format help -->
        <div class="form-group">
            <label for="urllinks">URL links</label>
            <textarea id="urllinks" name="urllinks" maxlength="500" rows="2"
                      placeholder="e.g. https://github.com/yourusername"
                      oninput="updateCount('urllinks', 500)"><?= esc($cv['URLlinks']) ?></textarea>
            <div class="form-help">GitHub, LinkedIn, portfolio — must start with https://</div>
            <div class="char-count" id="urllinks-count"></div>
        </div>

        <!-- Action buttons -->
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="view.php?id=<?= (int)$cv['id'] ?>" class="btn btn-secondary">View my CV</a>
        </div>
    </form>
</div>

<!-- Client-side character counter and validation -->
<script>
// Update character count display for a textarea
function updateCount(id, max) {
    var el = document.getElementById(id);
    var counter = document.getElementById(id + '-count');
    var len = el.value.length;
    counter.textContent = len + ' / ' + max;
    counter.className = 'char-count' + (len > max * 0.9 ? ' warn' : '');
}

// Initialise counters on page load
['profile', 'education', 'urllinks'].forEach(function(id) {
    updateCount(id, 500);
});

// Client-side form validation
document.getElementById('editForm').addEventListener('submit', function(e) {
    var name = document.getElementById('name').value.trim();
    if (name.length === 0) {
        e.preventDefault();
        alert('Name cannot be empty.');
    }
});
</script>

<?php require_once 'footer.php'; ?>
