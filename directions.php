<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/includes/layout.php';

$offices = db()->query(
    'SELECT * FROM offices ORDER BY floor, name'
)->fetchAll();

$spots = floor_spots();
$officeBySpot = [];
$routes = [];

foreach ($offices as $o) {
    $spotKey = (string)($o['map_spot'] ?? '');

    if ($spotKey === '' || !isset($spots[$spotKey])) {
        continue;
    }

    $spot = $spots[$spotKey];
    $officeBySpot[$spotKey] = $o;
    $status = office_status($o);

    $routes[(string)$o['id']] = [
        'spot' => $spotKey,
        'name' => $o['name'],
        'open' => $status['label'],
        'hours' => hours_text($o),
        'note' => $o['directions'] ?? '',
        'steps' => $spot['steps'],
        'segments' => $spot['segments'],
    ];
}

$selectedOffice = (string)($_GET['office'] ?? '');

if (!isset($routes[$selectedOffice])) {
    $selectedOffice = '';
}

$floorNames = floor_names();

page_head('Directions', 'directions');
?>

<h1>Indoor Office Directions</h1>
<p class="muted">
    Choose the office you want to visit. Follow the arrows on the sample floor plan.
</p>

<div class="alert warn">
    <strong>Sample floor plan only:</strong>
    This is an illustrative layout for demonstration. It may not match
    the actual municipal building.
</div>

<section class="card pad" id="map">
    <h2>Find your destination</h2>

    <label for="plan-office"><strong>Select an office</strong></label>
    <select id="plan-office" class="map-select">
        <option value="">-- Choose an office --</option>

        <?php foreach ($offices as $o): ?>
            <?php
            $id = (string)$o['id'];
            if (!isset($routes[$id])) {
                continue;
            }
            ?>
            <option value="<?= e($id) ?>"
                <?= $selectedOffice === $id ? 'selected' : '' ?>>
                <?= e($o['name']) ?> — <?= e($o['floor'] ?: 'Floor not specified') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (!$routes): ?>
        <p class="muted">
            No offices have been assigned to a map location yet.
            Please assign a Map spot in Admin &gt; Offices.
        </p>
    <?php endif; ?>

    <div class="plan-tabs" role="tablist" aria-label="Select floor">
        <?php foreach ($floorNames as $key => $name): ?>
            <button
                type="button"
                class="plan-tab <?= $key === 'ground' ? 'on' : '' ?>"
                data-floor="<?= e($key) ?>"
                role="tab"
                aria-selected="<?= $key === 'ground' ? 'true' : 'false' ?>">
                <?= e($name) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($floorNames as $key => $name): ?>
        <div
            class="plan-floor <?= $key === 'ground' ? 'on' : '' ?>"
            data-floor="<?= e($key) ?>">
            <h3><?= e($name) ?></h3>
            <div class="plan-scroll">
                <?php render_floor_svg($key, $officeBySpot); ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div id="plan-result" class="plan-result" hidden>
        <h2 id="plan-title"></h2>
        <p>
            <span id="plan-status" class="badge neutral"></span>
            <span id="plan-hours"></span>
        </p>

        <p id="plan-note" class="muted"></p>

        <h3>Walking directions</h3>
        <ol id="plan-steps" class="steps"></ol>

        <div class="actions">
            <button type="button" class="btn" id="plan-next" hidden>
                Next floor
            </button>
            <a class="btn ghost" id="plan-office-link" href="#">
                Office details
            </a>
        </div>
    </div>
</section>

<section class="card pad">
    <h2>Before you go</h2>
    <ol class="steps">
        <li>Choose the service or office you need.</li>
        <li>Check the office requirements before visiting.</li>
        <li>Follow the arrows from the main entrance on the floor plan.</li>
        <li>Ask the Public Assistance Desk if you need additional help.</li>
    </ol>
    <a class="btn" href="<?= e(url('services.php')) ?>">Find a service</a>
</section>

<script id="route-data" type="application/json"><?= json_encode(
    $routes,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
) ?></script>

<script id="floor-names" type="application/json"><?= json_encode(
    $floorNames,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
) ?></script>

<script src="<?= e(url('assets/map.js')) ?>" defer></script>

<?php page_foot('directions'); ?>
