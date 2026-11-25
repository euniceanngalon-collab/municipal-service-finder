<?php
declare(strict_types=1);
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/layout.php';

if (is_admin()) {
    redirect('admin/index.php');
}

const MAX_TRIES = 5;
const LOCK_SECONDS = 60;

$error = '';
$lockLeft = max(0, (int)($_SESSION['lock_until'] ?? 0) - time());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if ($lockLeft > 0) {
        $error = "Too many attempts. Try again in {$lockLeft} seconds.";
    } else {
        $u = trim((string)($_POST['username'] ?? ''));
        $p = (string)($_POST['password'] ?? '');
        $st = db()->prepare('SELECT * FROM admins WHERE username = ?');
        $st->execute([$u]);
        $row = $st->fetch();
        // always run a hash check so timing doesn't reveal valid usernames
        $hash = $row['password_hash'] ?? '$2y$10$usesomesillystringforsaltandnotarealhashxxxxxxxxxxxxxxxxxxxx';
        if (password_verify($p, $hash) && $row) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$row['id'];
            $_SESSION['admin_name'] = $row['username'];
            unset($_SESSION['tries'], $_SESSION['lock_until']);
            redirect('admin/index.php');
        }
        $_SESSION['tries'] = ($_SESSION['tries'] ?? 0) + 1;
        if ($_SESSION['tries'] >= MAX_TRIES) {
            $_SESSION['lock_until'] = time() + LOCK_SECONDS;
            $_SESSION['tries'] = 0;
            $lockLeft = LOCK_SECONDS;
            $error = 'Too many attempts. Try again in ' . LOCK_SECONDS . ' seconds.';
        } else {
            $error = 'Wrong username or password.';
        }
    }
}

page_head('Admin login', 'admin');
?>
<div class="card pad" style="max-width:420px;margin:24px auto">
    <h1>Admin login</h1>
    <p class="muted">For authorized LGU staff only.</p>
    <?php if ($error): ?><div class="alert err" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form" autocomplete="off">
        <?= csrf_field() ?>
        <label>Username<input name="username" required autofocus></label>
        <label>Password<input type="password" name="password" required></label>
        <button class="btn block" type="submit" <?= $lockLeft > 0 ? 'disabled' : '' ?>>Log in</button>
    </form>
</div>
<?php page_foot('admin');
