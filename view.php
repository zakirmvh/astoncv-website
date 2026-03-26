<?php
# view.php - shows full cv details for one person
# Zakir Mohammed - 250204760
# DG1IAD Internet Applications and Databases
# anyone can view this page (public)
# uses isSafeURL() to only make http/https links clickable

$pageTitle = 'AstonCV — View CV';
require_once 'header.php';

// make sure the id is a valid number
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    echo '<div class="alert alert-error">Invalid CV ID.</div>';
    require_once 'footer.php';
    exit;
}

$db = getDB();

// get the cv using prepared statement (prevents sql injection)
$stmt = $db->prepare("SELECT id, name, email, keyprogramming, profile, education, URLlinks FROM cvs WHERE id = :id");
$stmt->execute([':id' => (int)$_GET['id']]);
$cv = $stmt->fetch();

// if cv doesnt exist show error
if (!$cv) {
    echo '<div class="alert alert-error">CV not found.</div>';
    require_once 'footer.php';
    exit;
}
?>

<div class="card">
    <!-- name and avatar at the top -->
    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 24px;">
        <div class="avatar avatar-lg avatar-accent"><?= esc(getInitials($cv['name'])) ?></div>
        <div>
            <h2 style="margin-bottom: 0;"><?= esc($cv['name']) ?></h2>
            <div style="font-size: 13px; color: var(--accent);"><?= esc($cv['email']) ?></div>
        </div>
    </div>

    <!-- programming languages shown as tag pills -->
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

    <!-- profile summary -->
    <div class="cv-detail-row">
        <div class="cv-detail-label">Profile</div>
        <div class="cv-detail-value"><?= nl2br(esc($cv['profile'] ?: '—')) ?></div>
    </div>

    <!-- education -->
    <div class="cv-detail-row">
        <div class="cv-detail-label">Education</div>
        <div class="cv-detail-value"><?= nl2br(esc($cv['education'] ?: '—')) ?></div>
    </div>

    <!-- url links - only http/https get rendered as actual links for security -->
    <div class="cv-detail-row">
        <div class="cv-detail-label">Links</div>
        <div class="cv-detail-value">
            <?php if (!empty($cv['URLlinks'])): ?>
                <?php foreach (preg_split('/[\s,]+/', $cv['URLlinks']) as $url): ?>
                    <?php $url = trim($url); if (!empty($url)): ?>
                        <?php if (isSafeURL($url)): ?>
                            <!-- safe url - show as clickable link -->
                            <a href="<?= esc($url) ?>" target="_blank" rel="noopener noreferrer"><?= esc($url) ?></a><br>
                        <?php else: ?>
                            <!-- not a safe url - just show as text -->
                            <span style="color: var(--text-muted);"><?= esc($url) ?></span><br>
                        <?php endif; ?>
                    <?php endif; endforeach; ?>
            <?php else: ?>
                <span style="color: var(--text-dim);">—</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- buttons -->
    <div style="margin-top: 24px;">
        <a href="index.php" class="btn btn-secondary">&larr; Back</a>
        <?php // only show edit button if this is the logged in users own cv ?>
        <?php if (isLoggedIn() && $_SESSION['user_id'] == $cv['id']): ?>
            <a href="edit.php" class="btn btn-primary" style="margin-left: 8px;">Edit my CV</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
