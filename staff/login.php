<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/staff_auth.php';
require_once __DIR__ . '/../includes/layout.php';

if (is_staff()) {
    $staff = current_staff();

    if ($staff) {
        redirect('staff/dashboard.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = db()->prepare(
        'SELECT id, full_name, username, office_id, password_hash, is_active
           FROM staff
          WHERE username = ?
          LIMIT 1'
    );
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    // Use a dummy hash for unknown usernames to reduce timing differences.
    $dummyHash = '$2y$10$usesomesillystringforsaltandnotarealhashxxxxxxxxxxxxxxxxxxxx';
    $hash = $row['password_hash'] ?? $dummyHash;

    if (
        $row &&
        (int)$row['is_active'] === 1 &&
        !empty($row['office_id']) &&
        password_verify($password, $hash)
    ) {
        $office = db()->prepare(
            'SELECT id FROM offices WHERE id = ? LIMIT 1'
        );
        $office->execute([(int)$row['office_id']]);

        if ($office->fetch()) {
            session_regenerate_id(true);

            $_SESSION['staff_id'] = (int)$row['id'];
            $_SESSION['staff_name'] = $row['full_name'];
            $_SESSION['staff_office_id'] = (int)$row['office_id'];

            db()->prepare(
                'UPDATE staff SET last_login = NOW() WHERE id = ?'
            )->execute([(int)$row['id']]);

            redirect('staff/dashboard.php');
        }
    }

    $error = 'Invalid username or password, or your account is inactive.';
}

page_head('LGU Staff Login');
?>

<div class="card pad" style="max-width:440px;margin:32px auto">
    <h1>LGU Staff Login</h1>
    <p class="muted">For authorized municipal office staff.</p>

    <?php if ($error): ?>
        <div class="alert err" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form" autocomplete="off">
        <?= csrf_field() ?>

        <label>
            Username
            <input name="username" maxlength="50" required autofocus>
        </label>

        <label>
            Password
            <input type="password" name="password" required>
        </label>

        <button class="btn block" type="submit">Log in</button>
    </form>
</div>

<?php page_foot(); ?>

