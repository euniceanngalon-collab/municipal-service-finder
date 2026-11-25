<?php
declare(strict_types=1);
require __DIR__ . '/_admin.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            db()->prepare('DELETE FROM offices WHERE id = ?')->execute([$id]);
            flash('Office deleted.');
        } catch (PDOException) {
            flash('Cannot delete: this office still has services. Move or delete those services first.', 'error');
        }
    }
    redirect('admin/offices.php');
}

$rows = db()->query(
    'SELECT o.*, (SELECT COUNT(*) FROM services s WHERE s.office_id = o.id) AS total FROM offices o ORDER BY o.name'
)->fetchAll();

admin_head('Manage offices', 'offices');
?>
<div class="row-between"><h1>Offices</h1><a class="btn" href="<?= e(url('admin/office_form.php')) ?>">+ Add office</a></div>
<div class="tablewrap">
<table class="tbl">
    <thead><tr><th>Office</th><th>Location</th><th>Contact</th><th>Hours</th><th>Services</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><a href="<?= e(url('admin/office_form.php?id=' . (int)$r['id'])) ?>"><?= e($r['name']) ?></a></td>
            <td><?= e($r['location']) ?></td>
            <td><?= e($r['phone']) ?><br><small class="muted"><?= e($r['email']) ?></small></td>
            <td><?= e(hours_text($r)) ?></td>
            <td><?= (int)$r['total'] ?></td>
            <td class="acts">
                <a class="btn small ghost" href="<?= e(url('admin/office_form.php?id=' . (int)$r['id'])) ?>">Edit</a>
                <form method="post" data-confirm="Delete this office?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn small danger" type="submit">Delete</button></form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php admin_foot();
