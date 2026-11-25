<?php
declare(strict_types=1);

require __DIR__ . '/_admin.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $id = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');

    if ($id > 0 && $action === 'toggle') {
        $stmt = db()->prepare(
            'UPDATE staff
                SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END
              WHERE id = ?'
        );
        $stmt->execute([$id]);

        flash('Staff account status updated.');
    }

    redirect('admin/staff.php');
}

$rows = db()->query(
    'SELECT s.id, s.full_name, s.username, s.is_active,
            s.last_login, o.name AS office_name
       FROM staff s
       LEFT JOIN offices o ON o.id = s.office_id
      ORDER BY s.full_name'
)->fetchAll();

admin_head('Manage staff accounts', 'staff');
?>

<div class="row-between">
    <h1>Staff Accounts</h1>
    <a class="btn" href="<?= e(url('admin/staff_form.php')) ?>">+ Add Staff Account</a>
</div>

<p class="muted">
    Manage LGU staff accounts and their assigned offices.
</p>

<div class="tablewrap">
<table class="tbl">
    <thead>
        <tr>
            <th>Staff Name</th>
            <th>Username</th>
            <th>Assigned Office</th>
            <th>Status</th>
            <th>Last Login</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
    <?php if (!$rows): ?>
        <tr>
            <td colspan="6">No staff accounts have been created yet.</td>
        </tr>
    <?php endif; ?>

    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= e($r['full_name']) ?></td>
            <td><?= e($r['username']) ?></td>
            <td><?= e($r['office_name'] ?? 'No office assigned') ?></td>
            <td>
                <span class="tag <?= $r['is_active'] ? 'ok' : 'warn' ?>">
                    <?= $r['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
            </td>
            <td>
                <?= $r['last_login'] ? e($r['last_login']) : 'Never logged in' ?>
            </td>
            <td class="acts">
                <a class="btn small ghost"
                   href="<?= e(url('admin/staff_form.php?id=' . (int)$r['id'])) ?>">
                    Edit
                </a>

                <form method="post"
                      data-confirm="Change this staff account status?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="action" value="toggle">

                    <button class="btn small <?= $r['is_active'] ? 'danger' : 'ghost' ?>"
                            type="submit">
                        <?= $r['is_active'] ? 'Deactivate' : 'Activate' ?>
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php admin_foot(); ?>
