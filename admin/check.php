<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

foreach (['config.php', 'includes/floorplan.php', 'includes/layout.php', 'assets/map.js', 'admin/lgu.php'] as $f) {
    echo $f . ': ' . (is_file(__DIR__ . '/' . $f) ? 'found' : '<b style="color:red">MISSING</b>') . '<br>';
}

try {
    require __DIR__ . '/config.php';
    require __DIR__ . '/includes/layout.php';
    echo '<br>config + layout loaded OK<br>';
    echo 'LGU: ' . e(lgu_full_name()) . '<br>';
    echo 'Floor spots: ' . count(floor_spots()) . '<br>';
    $c = db()->query("SHOW COLUMNS FROM offices LIKE 'map_spot'")->fetch();
    echo 'map_spot column: ' . ($c ? 'yes' : '<b style="color:red">NO</b>') . '<br>';
} catch (Throwable $t) {
    echo '<br><b style="color:red">' . htmlspecialchars(get_class($t) . ': ' . $t->getMessage()) . '</b><br>';
    echo 'File: ' . htmlspecialchars($t->getFile()) . '<br>Line: ' . $t->getLine();
}