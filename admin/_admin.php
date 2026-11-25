<?php
declare(strict_types=1);
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/layout.php';

function admin_head(string $title, string $active): void
{
    require_admin();
    page_head($title, 'admin');
    $items = [
    'dash' => ['Dashboard', 'admin/index.php'],
    'services' => ['Services', 'admin/services.php'],
    'offices' => ['Offices', 'admin/offices.php'],
    'categories' => ['Categories', 'admin/categories.php'],
    'staff' => ['Staff Accounts', 'admin/staff.php'],
    'account' => ['Account', 'admin/account.php'],
];
    echo '<nav class="admin-nav" aria-label="Admin">';
    foreach ($items as $k => [$label, $href]) {
        echo '<a href="' . e(url($href)) . '"' . ($k === $active ? ' class="on"' : '') . '>' . e($label) . '</a>';
    }
    echo '<form method="post" action="' . e(url('admin/logout.php')) . '" style="margin-left:auto">' . csrf_field() . '<button class="btn small ghost" type="submit">Log out</button></form>';
    echo '</nav>';
    if ($f = flash()) {
        echo '<div class="alert ' . ($f['type'] === 'error' ? 'err' : 'ok') . '" role="status">' . e($f['msg']) . '</div>';
    }
}

function admin_foot(): void
{
    page_foot('admin');
}
