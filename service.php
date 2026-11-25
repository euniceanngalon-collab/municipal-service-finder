<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/includes/layout.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare(
    'SELECT s.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon
       FROM services s JOIN categories c ON c.id = s.category_id
      WHERE s.id = ? AND s.is_active = 1'
);
$st->execute([$id]);
$s = $st->fetch();
if (!$s) {
    http_response_code(404);
    page_head('Not found', 'services');
    echo '<div class="card pad"><h1>Service not found</h1><p><a href="' . e(url('services.php')) . '">Back to all services</a></p></div>';
    page_foot('services');
    exit;
}
$o = db()->prepare('SELECT * FROM offices WHERE id = ?');
$o->execute([$s['office_id']]);
$office = $o->fetch();

$reqs = lines($s['requirements']);
$steps = lines($s['steps']);

page_head($s['title'], 'services');
?>
<p class="crumbs"><a href="<?= e(url('services.php')) ?>">Services</a> › <a href="<?= e(url('services.php?cat=' . $s['category_slug'])) ?>"><?= e($s['category_name']) ?></a></p>
<span class="chip"><?= e($s['category_icon']) ?> <?= e($s['category_name']) ?></span>
<h1><?= e($s['title']) ?></h1>
<p class="lead"><?= e($s['summary']) ?></p>

<?php if (is_stale($s['last_verified'])): ?>
    <div class="alert warn">This information may be out of date. Please confirm with the office before you go.</div>
<?php endif; ?>

<div class="layout-2">
    <div>
        <?php if ($s['description']): ?>
            <section class="card pad"><h2>About this service</h2><p><?= nl2br(e($s['description'])) ?></p>
            <?php if ($s['who_can_apply']): ?><p><strong>Who can apply:</strong> <?= e($s['who_can_apply']) ?></p><?php endif; ?></section>
        <?php endif; ?>

        <section class="card pad">
            <h2>Requirements checklist</h2>
            <?php if ($reqs): ?>
                <p class="muted small">Tick each item as you prepare it. <span id="req-progress"></span></p>
                <ul class="checklist" data-key="svc-<?= (int)$s['id'] ?>">
                    <?php foreach ($reqs as $i => $r): ?>
                        <li><label><input type="checkbox" data-i="<?= $i ?>"> <span><?= e($r) ?></span></label></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="muted">Requirements not yet provided. Please contact the office.</p>
            <?php endif; ?>
        </section>

        <section class="card pad">
            <h2>Step-by-step procedure</h2>
            <?php if ($steps): ?>
                <ol class="steps">
                    <?php foreach ($steps as $step): ?><li><?= e($step) ?></li><?php endforeach; ?>
                </ol>
            <?php else: ?>
                <p class="muted">Steps not yet provided. Please contact the office.</p>
            <?php endif; ?>
        </section>
    </div>

    <aside>
        <section class="card pad">
            <h2>At a glance</h2>
            <dl class="facts">
                <dt>Fees</dt><dd><?= e($s['fees'] ?: 'Not yet provided') ?></dd>
                <dt>Processing time</dt><dd><?= e($s['processing_time'] ?: 'Not yet provided') ?></dd>
                <dt>Last verified</dt><dd><?= $s['last_verified'] ? e(date('M j, Y', strtotime($s['last_verified']))) : 'Not yet verified' ?></dd>
            </dl>
        </section>

        <section class="card pad">
            <h2>Where to go</h2>
            <h3><a href="<?= e(url('office.php?id=' . (int)$office['id'])) ?>"><?= e($office['name']) ?></a> <?= office_badge($office) ?></h3>
            <dl class="facts">
                <dt>Location</dt><dd><?= e($office['location']) ?></dd>
                <dt>Hours</dt><dd><?= e(hours_text($office)) ?><?php if ($office['hours_note']): ?><br><small class="muted"><?= e($office['hours_note']) ?></small><?php endif; ?></dd>
                <?php if ($office['phone']): ?><dt>Phone</dt><dd><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $office['phone'])) ?>"><?= e($office['phone']) ?></a></dd><?php endif; ?>
                <?php if ($office['email']): ?><dt>Email</dt><dd><a href="mailto:<?= e($office['email']) ?>"><?= e($office['email']) ?></a></dd><?php endif; ?>
            </dl>
            <?php if ($office['directions']): ?><p class="small"><strong>Finding it:</strong> <?= e($office['directions']) ?></p><?php endif; ?>
            <?php if (!empty($office['map_spot'])): ?><a class="btn block" href="<?= e(url('directions.php?office=' . (int)$office['id'] . '#map')) ?>">🧭 Show me the way (map)</a><?php endif; ?>
        </section>
        <button class="btn ghost block" type="button" onclick="window.print()">🖨 Print this page</button>
    </aside>
</div>
<?php page_foot('services');