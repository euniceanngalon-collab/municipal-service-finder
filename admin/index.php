<?php
declare(strict_types=1);
require __DIR__ . '/_admin.php';

$n = fn(string $sql) => (int)db()->query($sql)->fetchColumn();
$stats = [
    'Services' => $n('SELECT COUNT(*) FROM services'),
    'Offices' => $n('SELECT COUNT(*) FROM offices'),
    'Categories' => $n('SELECT COUNT(*) FROM categories'),
    'Searches (30 days)' => $n('SELECT COUNT(*) FROM search_logs WHERE created_at > NOW() - INTERVAL 30 DAY'),
];

$stale = db()->prepare(
    'SELECT id, title, last_verified FROM services
      WHERE is_active = 1 AND (last_verified IS NULL OR last_verified < CURDATE() - INTERVAL ' . STALE_DAYS . ' DAY)
      ORDER BY last_verified IS NULL DESC, last_verified LIMIT 10'
);
$stale->execute();
$stale = $stale->fetchAll();

$incomplete = db()->query(
    "SELECT id, title FROM services WHERE is_active = 1 AND (TRIM(IFNULL(requirements,'')) = '' OR TRIM(IFNULL(steps,'')) = '' OR fees = '') ORDER BY title LIMIT 10"
)->fetchAll();

$misses = db()->query(
    'SELECT query, COUNT(*) AS times FROM search_logs WHERE results_count = 0 AND created_at > NOW() - INTERVAL 30 DAY
      GROUP BY query ORDER BY times DESC, MAX(created_at) DESC LIMIT 10'
)->fetchAll();

$top = db()->query(
    'SELECT query, COUNT(*) AS times FROM search_logs WHERE created_at > NOW() - INTERVAL 30 DAY
      GROUP BY query ORDER BY times DESC LIMIT 10'
)->fetchAll();

admin_head('Dashboard', 'dash');
?>
<h1>Dashboard</h1>
<p class="muted">Signed in as <?= e($_SESSION['admin_name'] ?? '') ?>.</p>
<div class="stats">
    <?php foreach ($stats as $label => $v): ?>
        <div class="card stat"><strong><?= $v ?></strong><span class="muted"><?= e($label) ?></span></div>
    <?php endforeach; ?>
</div>

<div class="grid two">
    <section class="card pad">
        <h2>Needs verification</h2>
        <p class="muted small">Not verified in the last <?= STALE_DAYS ?> days. Users see a warning on these.</p>
        <?php if (!$stale): ?><p>All services are up to date. 🎉</p><?php else: ?>
            <ul><?php foreach ($stale as $s): ?>
                <li><a href="<?= e(url('admin/service_form.php?id=' . (int)$s['id'])) ?>"><?= e($s['title']) ?></a>
                    <small class="muted">(<?= $s['last_verified'] ? e($s['last_verified']) : 'never' ?>)</small></li>
            <?php endforeach; ?></ul>
        <?php endif; ?>
    </section>

    <section class="card pad">
        <h2>Incomplete information</h2>
        <p class="muted small">Missing requirements, steps or fees.</p>
        <?php if (!$incomplete): ?><p>Everything is filled in. 🎉</p><?php else: ?>
            <ul><?php foreach ($incomplete as $s): ?>
                <li><a href="<?= e(url('admin/service_form.php?id=' . (int)$s['id'])) ?>"><?= e($s['title']) ?></a></li>
            <?php endforeach; ?></ul>
        <?php endif; ?>
    </section>

    <section class="card pad">
        <h2>Searches with no results</h2>
        <p class="muted small">What residents looked for but did not find. Add or reword services to cover these.</p>
        <?php if (!$misses): ?><p class="muted">None yet.</p><?php else: ?>
            <ul><?php foreach ($misses as $m): ?><li>“<?= e($m['query']) ?>” <small class="muted">× <?= (int)$m['times'] ?></small></li><?php endforeach; ?></ul>
        <?php endif; ?>
    </section>

    <section class="card pad">
        <h2>Top searches</h2>
        <?php if (!$top): ?><p class="muted">No searches yet.</p><?php else: ?>
            <ul><?php foreach ($top as $m): ?><li>“<?= e($m['query']) ?>” <small class="muted">× <?= (int)$m['times'] ?></small></li><?php endforeach; ?></ul>
        <?php endif; ?>
    </section>
</div>
<?php admin_foot();
