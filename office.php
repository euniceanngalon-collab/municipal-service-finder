<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/includes/layout.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM offices WHERE id = ?');
$st->execute([$id]);
$o = $st->fetch();
if (!$o) {
    http_response_code(404);
    page_head('Not found', 'offices');
    echo '<div class="card pad"><h1>Office not found</h1><p><a href="' . e(url('offices.php')) . '">Back to offices</a></p></div>';
    page_foot('offices');
    exit;
}
$ss = db()->prepare(
    'SELECT s.*, c.name AS category_name, c.icon AS category_icon, ? AS office_name
       FROM services s JOIN categories c ON c.id = s.category_id
      WHERE s.office_id = ? AND s.is_active = 1 ORDER BY s.title'
);
$ss->execute([$o['name'], $o['id']]);
$services = $ss->fetchAll();

page_head($o['name'], 'offices');
?>
<p class="crumbs"><a href="<?= e(url('offices.php')) ?>">Offices</a> › <?= e($o['name']) ?></p>
<h1><?= e($o['name']) ?> <?= office_badge($o) ?></h1>
<p class="lead"><?= e($o['description']) ?></p>

<div class="layout-2">
    <section class="card pad">
        <h2>Contact and hours</h2>
        <dl class="facts">
            <dt>Location</dt><dd><?= e($o['location']) ?></dd>
            <?php if ($o['floor']): ?><dt>Floor</dt><dd><?= e($o['floor']) ?></dd><?php endif; ?>
            <dt>Office hours</dt><dd><?= e(hours_text($o)) ?><?php if ($o['hours_note']): ?><br><small class="muted"><?= e($o['hours_note']) ?></small><?php endif; ?></dd>
            <?php if ($o['phone']): ?><dt>Phone</dt><dd><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $o['phone'])) ?>"><?= e($o['phone']) ?></a></dd><?php endif; ?>
            <?php if ($o['email']): ?><dt>Email</dt><dd><a href="mailto:<?= e($o['email']) ?>"><?= e($o['email']) ?></a></dd><?php endif; ?>
        </dl>
        <?php if ($o['directions']): ?><h3>How to find this office</h3><p><?= e($o['directions']) ?></p><?php endif; ?>
        <?php if (!empty($o['map_spot'])): ?><p><a class="btn" href="<?= e(url('directions.php?office=' . (int)$o['id'] . '#map')) ?>">🧭 Show me the way (map)</a></p><?php endif; ?>
    </section>

    <section>
        <h2>Services handled here</h2>
        <?php if (!$services): ?>
            <p class="muted">No services listed yet.</p>
        <?php else: ?>
            <div class="stack">
                <?php foreach ($services as $s) { service_card($s); } ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php page_foot('offices');