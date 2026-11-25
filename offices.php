<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/includes/layout.php';

$offices = db()->query(
    'SELECT o.*, (SELECT COUNT(*) FROM services s WHERE s.office_id = o.id AND s.is_active = 1) AS total
       FROM offices o ORDER BY o.name'
)->fetchAll();

page_head('Offices', 'offices');
?>
<h1>Office directory</h1>
<p class="muted">Find which office handles what, where it is, and when it is open.</p>
<?php disclaimer(); ?>
<div class="grid two">
    <?php foreach ($offices as $o): ?>
        <article class="card service">
            <h3><a href="<?= e(url('office.php?id=' . (int)$o['id'])) ?>"><?= e($o['name']) ?></a></h3>
            <?= office_badge($o) ?>
            <p class="muted"><?= e($o['description']) ?></p>
            <ul class="meta">
                <li>📍 <?= e($o['location']) ?></li>
                <li>🕘 <?= e(hours_text($o)) ?></li>
                <?php if ($o['phone']): ?><li>📞 <?= e($o['phone']) ?></li><?php endif; ?>
            </ul>
            <a class="btn small" href="<?= e(url('office.php?id=' . (int)$o['id'])) ?>">View office (<?= (int)$o['total'] ?> services) →</a>
        </article>
    <?php endforeach; ?>
</div>
<?php page_foot('offices');
