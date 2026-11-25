<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/staff_auth.php';
require_once __DIR__ . '/../includes/layout.php';

$staff = require_staff();

$stmt = db()->prepare(
    'SELECT id, name FROM offices WHERE id = ? LIMIT 1'
);
$stmt->execute([(int)$staff['office_id']]);
$office = $stmt->fetch();

$stmt = db()->prepare(
    'SELECT COUNT(*) FROM services WHERE office_id = ?'
);
$stmt->execute([(int)$staff['office_id']]);
$totalServices = (int)$stmt->fetchColumn();

$stmt = db()->prepare(
    'SELECT COUNT(*) FROM services WHERE office_id = ? AND is_active = 1'
);
$stmt->execute([(int)$staff['office_id']]);
$visibleServices = (int)$stmt->fetchColumn();

page_head('Staff Dashboard');
?>

<div class="row-between">
    <h1>Staff Dashboard</h1>

    <form method="post" action="<?= e(url('staff/logout.php')) ?>">
        <?= csrf_field() ?>
        <button class="btn ghost" type="submit">Log out</button>
    </form>
</div>

<div class="card pad">
    <h2>Welcome, <?= e($staff['full_name']) ?></h2>
    <p class="muted">Assigned office: <?= e($office['name'] ?? 'Office not found') ?></p>
</div>

<div class="cols" style="margin-top:20px">
    <div class="card pad">
        <h2><?= $totalServices ?></h2>
        <p class="muted">Total services assigned to your office</p>
    </div>

    <div class="card pad">
        <h2><?= $visibleServices ?></h2>
        <p class="muted">Services visible to the public</p>
    </div>
</div>

<div class="card pad" style="margin-top:20px">
    <h2>Office Management</h2>
    <p>Manage information and services belonging to your assigned office.</p>

    <div class="actions">
        <a class="btn" href="<?= e(url('staff/office.php')) ?>">Manage Office Information</a>
        <a class="btn" href="<?= e(url('staff/services.php')) ?>">Manage Services</a>
    </div>
</div>

<?php page_foot(); ?>
