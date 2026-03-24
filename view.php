<?php
/**
 * view.php — View CV Details
 * 
 * @author  Zakir Mohammed
 * @student 250204760
 * @module  DG1IAD Internet Applications and Databases
 * 
 * Displays the full CV of a user including name, email,
 * programming languages, profile, education, and URL links.
 * Accessible to all users (public page).
 * 
 * Security: URL links are validated to only render http/https
 * URLs as clickable links. All other content is escaped.
 */
$pageTitle = 'AstonCV — View CV';
require_once 'header.php';

// Validate the ID parameter (must be a positive integer)
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    echo '<div class="alert alert-error">Invalid CV ID.</div>';
    require_once 'footer.php';
    exit;
}

$db = getDB();

// Fetch CV using prepared statement (prevents SQL injection)
$stmt = $db->prepare("SELECT id, name, email, keyprogramming, profile, education, URLlinks FROM cvs WHERE id = :id");
$stmt->execute([':id' => (int)$_GET['id']]);
$cv = $stmt->fetch();

// Show error if CV not found
if (!$cv) {
    echo '<div class="alert alert-error">CV not found.</div>';
    require_once 'footer.php';
    exit;
}
?>

<div class="card">
    <!-- CV header with avatar and name -->
    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 24px;">
        <div class="avatar avatar-lg avatar-accent"><?= esc(getInitials($cv['name'])) ?></div>
        <div>
            <h2 style="margin-bottom: 0;"><?= esc($cv['name']) ?></h2>
            <div style="font-size: 13px; color: var(--accent);"><?= esc($cv['email']) ?></div>
        </div>
    </div>

    <!-- Key programming languages (displayed as tags) -->
    <div class="cv-detail-row">
        <div class="cv-detail-label">Key languages</div>
        <div class="cv-detail-value">
            <?php if (!empty($cv['keyprogramming'])): ?>
                <div class="tags">
                    <?php foreach (array_map('trim', explode(',', $cv['keyprogramming'])) as $lang): ?>
                        <span class="tag tag-accent"><?= esc($lang) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <span style="color: var(--text-dim);">—</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Profile / summary -->
    <div class="cv-detail-row">
        <div class="cv-detail-label">Profile</div>
        <div class="cv-detail-value"><?= nl2br(esc($cv['profile'] ?: '—')) ?></div>
    </div>

    <!-- Education -->
    <div class="cv-detail-row">
        <div class="cv-detail-label">Education</div>
        <div class="cv-detail-value"><?= nl2br(esc($cv['education'] ?: '—')) ?></div>
    </div>

    <!-- URL links (only http/https are rendered as clickable links) -->
    <div class="cv-detail-row">
        <div class="cv-detail-label">Links</div>
        <div class="cv-detail-value">
            <?php if (!empty($cv['URLlinks'])): ?>
                <?php foreach (preg_split('/[\s,]+/', $cv['URLlinks']) as $url): ?>
                    <?php $url = trim($url); if (!empty($url)): ?>
                        <?php if (isSafeURL($url)): ?>
                            <!-- Only render as clickable if URL starts with http:// or https:// -->
                            <a href="<?= esc($url) ?>" target="_blank" rel="noopener noreferrer"><?= esc($url) ?></a><br>
                        <?php else: ?>
                            <!-- Display non-http URLs as plain text for safety -->
                            <span style="color: var(--text-muted);"><?= esc($url) ?></span><br>
                        <?php endif; ?>
                    <?php endif; endforeach; ?>
            <?php else: ?>
                <span style="color: var(--text-dim);">—</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Action buttons -->
    <div style="margin-top: 24px;">
        <a href="index.php" class="btn btn-secondary">&larr; Back</a>
        <?php if (isLoggedIn() && $_SESSION['user_id'] == $cv['id']): ?>
            <a href="edit.php" class="btn btn-primary" style="margin-left: 8px;">Edit my CV</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
