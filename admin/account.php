<?php
declare(strict_types=1);
require __DIR__ . '/_admin.php';
require_admin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $cur = (string)($_POST['current'] ?? '');
    $new = (string)($_POST['new'] ?? '');
    $conf = (string)($_POST['confirm'] ?? '');
    $st = db()->prepare('SELECT password_hash FROM admins WHERE id = ?');
    $st->execute([$_SESSION['admin_id']]);
    $hash = $st->fetchColumn();
    if (!$hash || !password_verify($cur, (string)$hash)) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $conf) {
        $error = 'New passwords do not match.';
    } else {
        db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
        flash('Password changed.');
        redirect('admin/account.php');
    }
}

admin_head('Account', 'account');
?>
<h1>Change password</h1>
<?php if ($error): ?><div class="alert err" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form card pad" style="max-width:460px" autocomplete="off">
    <?= csrf_field() ?>
    <label>Current password<input type="password" name="current" required></label>
    <label>New password<input type="password" name="new" minlength="8" required></label>
    <label>Confirm new password<input type="password" name="confirm" minlength="8" required></label>
    <button class="btn" type="submit">Change password</button>
</form>
<?php admin_foot();
