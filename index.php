<?php
/**
 * index.php — Homepage / All CVs
 * 
 * @author  Zakir Mohammed
 * @student 250204760
 * @module  DG1IAD Internet Applications and Databases
 * 
 * Displays all CVs in two formats:
 * 1. A featured card for the first CV
 * 2. A card grid for remaining CVs
 * 3. A quick-view table listing all CVs
 * 
 * Also shows a search bar, hero section, and live stats.
 * This page is accessible to all users (public).
 */
$pageTitle = 'AstonCV — Find Talented Programmers';
require_once 'header.php';

$db = getDB();

// Fetch all CVs ordered by ID (first registered = featured)
$stmt = $db->query("SELECT id, name, email, keyprogramming, profile FROM cvs ORDER BY id ASC");
$cvs = $stmt->fetchAll();

// Count unique programming languages across all CVs
$allLangs = [];
foreach ($cvs as $cv) {
    if (!empty($cv['keyprogramming'])) {
        $langs = array_map('trim', preg_split('/[,;]+/', $cv['keyprogramming']));
        $allLangs = array_merge($allLangs, $langs);
    }
}
$langCount = count(array_unique($allLangs));
?>

<!-- Hero section with tagline -->
<div class="hero">
    <h1>Find talented <span>programmers</span></h1>
    <p>Browse CVs from Aston University developers. Search by skill, connect by interest.</p>
</div>

<!-- Search bar (links to search.php) -->
<form method="GET" action="search.php" class="search-bar">
    <input type="text" name="q" placeholder="Search name or skill..." maxlength="100">
    <button type="submit">Go</button>
</form>

<!-- Live statistics ribbon -->
<div class="stats">
    <div class="stat">
        <div class="stat-number"><?= count($cvs) ?></div>
        <div class="stat-label">Developers</div>
    </div>
    <div class="stat">
        <div class="stat-number accent"><?= $langCount ?></div>
        <div class="stat-label">Skills</div>
    </div>
    <div class="stat">
        <div class="stat-number">2026</div>
        <div class="stat-label">Cohort</div>
    </div>
</div>

<?php if (count($cvs) === 0): ?>
    <!-- Empty state when no CVs exist -->
    <div class="card">
        <p style="color: var(--text-muted);">No CVs yet. <a href="register.php">Register</a> to add yours!</p>
    </div>
<?php else: ?>

    <?php
    // First CV is displayed as a "featured" card
    $featured = $cvs[0];
    $others = array_slice($cvs, 1);
    ?>

    <!-- Featured CV card (highlighted with green border) -->
    <a href="view.php?id=<?= (int)$featured['id'] ?>" class="featured-card" style="display:block; color:inherit; text-decoration:none;">
        <span class="featured-badge">Featured</span>
        <div class="cv-card-header">
            <div class="avatar avatar-lg avatar-accent"><?= esc(getInitials($featured['name'])) ?></div>
            <div>
                <div class="cv-name" style="font-size:16px;"><?= esc($featured['name']) ?></div>
                <div class="cv-email" style="color: var(--accent);"><?= esc($featured['email']) ?></div>
            </div>
        </div>
        <?php if (!empty($featured['profile'])): ?>
            <div class="featured-bio"><?= esc(mb_strimwidth($featured['profile'], 0, 150, '...')) ?></div>
        <?php endif; ?>
        <?php if (!empty($featured['keyprogramming'])): ?>
            <div class="tags">
                <?php foreach (array_map('trim', explode(',', $featured['keyprogramming'])) as $lang): ?>
                    <span class="tag tag-accent"><?= esc($lang) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </a>

    <!-- Remaining CVs in a 2-column card grid -->
    <?php if (count($others) > 0): ?>
        <div class="section-label">All programmers</div>
        <div class="cv-grid">
            <?php foreach ($others as $cv): ?>
                <a href="view.php?id=<?= (int)$cv['id'] ?>" class="cv-card">
                    <div class="cv-card-header">
                        <div class="avatar"><?= esc(getInitials($cv['name'])) ?></div>
                        <div>
                            <div class="cv-name"><?= esc($cv['name']) ?></div>
                            <div class="cv-email"><?= esc($cv['email']) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($cv['keyprogramming'])): ?>
                        <div class="tags">
                            <?php foreach (array_map('trim', explode(',', $cv['keyprogramming'])) as $lang): ?>
                                <span class="tag"><?= esc($lang) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Quick-view table of all CVs -->
    <div class="section-label" style="margin-top: 8px;">Quick view</div>
    <div class="cv-table-wrap">
        <table class="cv-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Languages</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cvs as $cv): ?>
                <tr>
                    <td><a href="view.php?id=<?= (int)$cv['id'] ?>"><?= esc($cv['name']) ?></a></td>
                    <td><?= esc($cv['email']) ?></td>
                    <td><?= esc($cv['keyprogramming'] ?: '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php require_once 'footer.php'; ?>
