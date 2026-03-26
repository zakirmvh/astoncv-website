<?php
# search.php - search all cvs by name or programming language
# Zakir Mohammed - 250204760
# DG1IAD Internet Applications and Databases
# public page - anyone can search

$pageTitle = 'AstonCV — Search';
require_once 'header.php';

$results = [];
$searchTerm = '';
$searched = false;

// if user submitted a search query
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
    $searchTerm = trim($_GET['q']);
    $searched = true;

    if (!empty($searchTerm)) {
        $db = getDB();

        // search using LIKE with prepared statement so its safe from sql injection
        $stmt = $db->prepare(
            "SELECT id, name, email, keyprogramming 
             FROM cvs 
             WHERE name LIKE :term OR keyprogramming LIKE :term2
             ORDER BY name ASC"
        );
        $like = '%' . $searchTerm . '%';
        $stmt->execute([':term' => $like, ':term2' => $like]);
        $results = $stmt->fetchAll();
    }
}
?>

<div style="padding-top: 20px;">
    <div class="section-label">Search CVs</div>
    <p style="color: var(--text-dim); font-size: 14px; margin-bottom: 20px;">Find developers by name or programming language.</p>

    <!-- search form -->
    <form method="GET" action="search.php" class="search-bar" style="max-width: 100%; margin-bottom: 24px;">
        <input type="text" name="q" placeholder="e.g. Java, Python, Zakir..."
               value="<?= esc($searchTerm) ?>" required maxlength="100">
        <button type="submit">Search</button>
    </form>

    <?php if ($searched): ?>
        <?php if (empty($searchTerm)): ?>
            <div class="alert alert-error">Please enter a search term.</div>
        <?php elseif (count($results) === 0): ?>
            <!-- no results message -->
            <div class="card" style="text-align: center; padding: 40px;">
                <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 8px;">No results found</p>
                <p style="color: var(--text-dim); font-size: 13px;">No CVs match &ldquo;<?= esc($searchTerm) ?>&rdquo;. Try a different name or language.</p>
            </div>
        <?php else: ?>
            <!-- show how many results we got -->
            <div class="section-label"><?= count($results) ?> result(s) for &ldquo;<?= esc($searchTerm) ?>&rdquo;</div>
            <div class="cv-grid">
                <?php foreach ($results as $cv): ?>
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
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
