<?php
# edit.php - lets logged in users edit their cv
# Zakir Mohammed - 250204760
# DG1IAD Internet Applications and Databases
#
# security: authorization (must be logged in), csrf, server side validation,
# url validation, client side js validation and char counters

$pageTitle = 'AstonCV — Edit My CV';
require_once 'header.php';

// authorization - redirect to login if not logged in
if (!isLoggedIn()) { redirect('login.php'); }

$db = getDB();
$errors = [];

// get the current users cv data
$stmt = $db->prepare("SELECT * FROM cvs WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$cv = $stmt->fetch();

// if user doesnt exist anymore destroy the session
if (!$cv) {
    session_destroy();
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // check csrf token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    // trim all the inputs
    $name           = trim($_POST['name'] ?? '');
    $keyprogramming = trim($_POST['keyprogramming'] ?? '');
    $profile        = trim($_POST['profile'] ?? '');
    $education      = trim($_POST['education'] ?? '');
    $urllinks       = trim($_POST['urllinks'] ?? '');

    # server side validation - check lengths
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

    // check urls are valid (must start with http or https)
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
        // update the cv in the database
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

        // update the name in the session too
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

        <!-- name and email side by side -->
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

        <!-- programming languages -->
        <div class="form-group">
            <label for="keyprogramming">Key programming languages</label>
            <input type="text" id="keyprogramming" name="keyprogramming"
                   value="<?= esc($cv['keyprogramming']) ?>" maxlength="255"
                   placeholder="e.g. Java, Python, JavaScript">
            <div class="form-help">Separate multiple languages with commas.</div>
        </div>

        <!-- profile with character counter -->
        <div class="form-group">
            <label for="profile">Profile / summary</label>
            <textarea id="profile" name="profile" maxlength="500" rows="4"
                      placeholder="A brief summary about yourself..."
                      oninput="updateCount('profile', 500)"><?= esc($cv['profile']) ?></textarea>
            <div class="char-count" id="profile-count"></div>
        </div>

        <!-- education with character counter -->
        <div class="form-group">
            <label for="education">Education</label>
            <textarea id="education" name="education" maxlength="500" rows="3"
                      placeholder="e.g. BSc Computer Science, Aston University"
                      oninput="updateCount('education', 500)"><?= esc($cv['education']) ?></textarea>
            <div class="char-count" id="education-count"></div>
        </div>

        <!-- urls with counter and help text -->
        <div class="form-group">
            <label for="urllinks">URL links</label>
            <textarea id="urllinks" name="urllinks" maxlength="500" rows="2"
                      placeholder="e.g. https://github.com/yourusername"
                      oninput="updateCount('urllinks', 500)"><?= esc($cv['URLlinks']) ?></textarea>
            <div class="form-help">GitHub, LinkedIn, portfolio — must start with https://</div>
            <div class="char-count" id="urllinks-count"></div>
        </div>

        <!-- save and view buttons -->
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="view.php?id=<?= (int)$cv['id'] ?>" class="btn btn-secondary">View my CV</a>
        </div>
    </form>
</div>

<!-- javascript for character counters and validation -->
<script>
// updates the character count under a textarea
function updateCount(id, max) {
    var el = document.getElementById(id);
    var counter = document.getElementById(id + '-count');
    var len = el.value.length;
    counter.textContent = len + ' / ' + max;
    // turns red when getting close to the limit
    counter.className = 'char-count' + (len > max * 0.9 ? ' warn' : '');
}

// set up counters when page loads
['profile', 'education', 'urllinks'].forEach(function(id) {
    updateCount(id, 500);
});

// quick validation before submitting
document.getElementById('editForm').addEventListener('submit', function(e) {
    var name = document.getElementById('name').value.trim();
    if (name.length === 0) {
        e.preventDefault();
        alert('Name cannot be empty.');
    }
});
</script>

<?php require_once 'footer.php'; ?>
