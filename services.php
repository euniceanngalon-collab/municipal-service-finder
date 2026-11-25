<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/includes/layout.php';

$q = trim((string)($_GET['q'] ?? ''));
$catSlug = trim((string)($_GET['cat'] ?? ''));

$cats = db()->query('SELECT * FROM categories ORDER BY sort_order')->fetchAll();
$activeCat = null;
foreach ($cats as $c) {
    if ($c['slug'] === $catSlug) {
        $activeCat = $c;
    }
}

if ($q !== '') {
    $results = search_services(mb_substr($q, 0, 200));
    if ($activeCat) {
        $results = array_values(array_filter($results, fn($r) => $r['category_slug'] === $activeCat['slug']));
    }
    // log the search so admins can see what people look for (and what is missing)
    db()->prepare('INSERT INTO search_logs (query, results_count) VALUES (?, ?)')->execute([mb_substr($q, 0, 255), count($results)]);
} else {
    $sql = 'SELECT s.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon, o.name AS office_name
              FROM services s JOIN categories c ON c.id = s.category_id JOIN offices o ON o.id = s.office_id
             WHERE s.is_active = 1';
    $args = [];
    if ($activeCat) {
        $sql .= ' AND c.id = ?';
        $args[] = $activeCat['id'];
    }
    $st = db()->prepare($sql . ' ORDER BY c.sort_order, s.title');
    $st->execute($args);
    $results = $st->fetchAll();
}

page_head($activeCat ? $activeCat['name'] : 'Services', 'services');
?>
<h1><?= $activeCat ? e($activeCat['icon'] . ' ' . $activeCat['name']) : 'All services' ?></h1>
<?php if ($activeCat): ?><p class="muted"><?= e($activeCat['description']) ?></p><?php endif; ?>

<form class="searchbar" action="" method="get" role="search">
    <?php if ($activeCat): ?><input type="hidden" name="cat" value="<?= e($activeCat['slug']) ?>"><?php endif; ?>
    <label for="q" class="sr">Search</label>
    <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Describe what you need…">
    <button class="btn" type="submit">Search</button>
</form>

<div class="filters" role="group" aria-label="Categories">
    <a class="pill <?= !$activeCat ? 'on' : '' ?>" href="<?= e(url('services.php' . ($q !== '' ? '?q=' . urlencode($q) : ''))) ?>">All</a>
    <?php foreach ($cats as $c): ?>
        <a class="pill <?= $activeCat && $activeCat['id'] === $c['id'] ? 'on' : '' ?>"
           href="<?= e(url('services.php?cat=' . $c['slug'] . ($q !== '' ? '&q=' . urlencode($q) : ''))) ?>"><?= e($c['icon'] . ' ' . $c['name']) ?></a>
    <?php endforeach; ?>
</div>

<?php if ($q !== ''): ?>
    <p class="muted"><?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?> for “<?= e($q) ?>”</p>
<?php endif; ?>

<?php if (!$results): ?>
    <div class="card pad empty">
        <h3>No matching service found</h3>
        <p>Try other words (for example “permit”, “certificate”, “ID”), choose a category above, or ask at the Public Assistance Desk.</p>
        <a class="btn ghost" href="<?= e(url('directions.php')) ?>">How to get assistance</a>
    </div>
<?php else: ?>
    <div class="grid two">
        <?php foreach ($results as $s) { service_card($s); } ?>
    </div>
<?php endif; ?>
<?php page_foot('services');
