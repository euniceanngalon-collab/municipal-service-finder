<?php
declare(strict_types=1);
require __DIR__ . '/_admin.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        db()->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
        flash('Service deleted.');
    } elseif ($action === 'toggle') {
        db()->prepare('UPDATE services SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
        flash('Service visibility changed.');
    } elseif ($action === 'verify') {
        db()->prepare('UPDATE services SET last_verified = CURDATE() WHERE id = ?')->execute([$id]);
        flash('Marked as verified today.');
    }
    redirect('admin/services.php');
}

$rows = db()->query(
    'SELECT s.id, s.title, s.is_active, s.last_verified, c.name AS category, o.name AS office
       FROM services s JOIN categories c ON c.id = s.category_id JOIN offices o ON o.id = s.office_id
      ORDER BY c.sort_order, s.title'
)->fetchAll();

admin_head('Manage services', 'services');
?>
<div class="row-between"><h1>Services</h1><a class="btn" href="<?= e(url('admin/service_form.php')) ?>">+ Add service</a></div>
<div class="tablewrap">
<table class="tbl">
    <thead><tr><th>Service</th><th>Category</th><th>Office</th><th>Status</th><th>Verified</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><a href="<?= e(url('admin/service_form.php?id=' . (int)$r['id'])) ?>"><?= e($r['title']) ?></a></td>
            <td><?= e($r['category']) ?></td>
            <td><?= e($r['office']) ?></td>
            <td><span class="tag <?= $r['is_active'] ? 'ok' : '' ?>"><?= $r['is_active'] ? 'Visible' : 'Hidden' ?></span></td>
            <td><?= $r['last_verified'] ? e($r['last_verified']) : '<span class="tag warn">never</span>' ?></td>
            <td class="acts">
                <a class="btn small ghost" href="<?= e(url('admin/service_form.php?id=' . (int)$r['id'])) ?>">Edit</a>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="verify"><button class="btn small ghost" type="submit">Verified today</button></form>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="btn small ghost" type="submit"><?= $r['is_active'] ? 'Hide' : 'Show' ?></button></form>
                <form method="post" data-confirm="Delete this service permanently?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn small danger" type="submit">Delete</button></form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php admin_foot();
