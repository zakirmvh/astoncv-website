<?php
# index.php - homepage showing all the cvs
# Zakir Mohammed - 250204760
# DG1IAD Internet Applications and Databases
# 
# this page shows all cvs in cards and a table
# anyone can see this page (public)

$pageTitle = 'AstonCV — Find Talented Programmers';
require_once 'header.php';

$db = getDB();

// get all cvs from the database ordered by id
$stmt = $db->query("SELECT id, name, email, keyprogramming, profile FROM cvs ORDER BY id ASC");
$cvs = $stmt->fetchAll();

// count how many different programming languages there are
$allLangs = [];
foreach ($cvs as $cv) {
    if (!empty($cv['keyprogramming'])) {
        $langs = array_map('trim', preg_split('/[,;]+/', $cv['keyprogramming']));
        $allLangs = array_merge($allLangs, $langs);
    }
}
$langCount = count(array_unique($allLangs));
?>

<!-- hero section with the main heading -->
<div class="hero">
    <h1>Find talented <span>programmers</span></h1>
    <p>Browse CVs from Aston University developers. Search by skill, connect by interest.</p>
</div>

<!-- search bar that links to search.php -->
<form method="GET" action="search.php" class="search-bar">
    <input type="text" name="q" placeholder="Search name or skill..." maxlength="100">
    <button type="submit">Go</button>
</form>

<!-- stats showing total developers, skills and year -->
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
    <!-- if theres no cvs yet show this message -->
    <div class="card">
        <p style="color: var(--text-muted);">No CVs yet. <a href="register.php">Register</a> to add yours!</p>
    </div>
<?php else: ?>

    <?php
    // first cv gets the featured card, rest go in the grid
    $featured = $cvs[0];
    $others = array_slice($cvs, 1);
    ?>

    <!-- featured cv card with green border -->
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

    <!-- rest of the cvs in a 2 column grid -->
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

    <!-- table view at the bottom for quick browsing -->
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
