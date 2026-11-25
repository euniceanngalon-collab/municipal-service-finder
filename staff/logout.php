<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/staff_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    staff_logout_session();
    session_regenerate_id(true);
}

redirect('staff/login.php');
