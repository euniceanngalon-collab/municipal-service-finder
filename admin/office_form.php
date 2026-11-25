<?php
declare(strict_types=1);
require __DIR__ . '/_admin.php';
require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$o = [
    'name' => '', 'description' => '', 'location' => '', 'floor' => '', 'directions' => '', 'phone' => '', 'email' => '',
    'open_days' => '1,2,3,4,5', 'open_time' => '08:00:00', 'close_time' => '17:00:00', 'hours_note' => '', 'map_spot' => '',
];
if ($id) {
    $st = db()->prepare('SELECT * FROM offices WHERE id = ?');
    $st->execute([$id]);
    $found = $st->fetch();
    if (!$found) {
        flash('Office not found.', 'error');
        redirect('admin/offices.php');
    }
    $o = $found;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $days = array_values(array_filter(array_map('intval', (array)($_POST['open_days'] ?? [])), fn($d) => $d >= 1 && $d <= 7));
    sort($days);
    $time = fn(string $k) => preg_match('/^\d{2}:\d{2}(:\d{2})?$/', (string)($_POST[$k] ?? '')) ? substr($_POST[$k] . ':00', 0, 8) : null;
    $f = [
        'name' => trim((string)($_POST['name'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'location' => trim((string)($_POST['location'] ?? '')),
        'floor' => trim((string)($_POST['floor'] ?? '')),
        'directions' => trim((string)($_POST['directions'] ?? '')),
        'phone' => trim((string)($_POST['phone'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'open_days' => implode(',', $days),
        'open_time' => $time('open_time'),
        'close_time' => $time('close_time'),
        'hours_note' => trim((string)($_POST['hours_note'] ?? '')),
        'map_spot' => (string)($_POST['map_spot'] ?? ''),
    ];
    if ($f['map_spot'] !== '' && !isset(floor_spots()[$f['map_spot']])) { $f['map_spot'] = ''; }
    if ($f['name'] === '') { $errors[] = 'Office name is required.'; }
    if ($f['email'] !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email address is not valid.'; }
    if ($f['open_time'] && $f['close_time'] && $f['close_time'] <= $f['open_time']) { $errors[] = 'Closing time must be after opening time.'; }
    $o = array_merge($o, $f);

    if (!$errors) {
        $cols = array_keys($f);
        if ($id) {
            $sql = 'UPDATE offices SET ' . implode(', ', array_map(fn($c) => "$c = :$c", $cols)) . ' WHERE id = :id';
            db()->prepare($sql)->execute($f + ['id' => $id]);
            flash('Office updated.');
        } else {
            $sql = 'INSERT INTO offices (' . implode(',', $cols) . ') VALUES (' . implode(',', array_map(fn($c) => ":$c", $cols)) . ')';
            db()->prepare($sql)->execute($f);
            flash('Office added.');
        }
        redirect('admin/offices.php');
    }
}

$selDays = array_filter(explode(',', (string)$o['open_days']), 'strlen');
$dayNames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];

admin_head($id ? 'Edit office' : 'Add office', 'offices');
?>
<h1><?= $id ? 'Edit office' : 'Add office' ?></h1>
<?php foreach ($errors as $er): ?><div class="alert err" role="alert"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" class="form card pad">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
    <label>Office name *<input name="name" maxlength="120" required value="<?= e($o['name']) ?>"></label>
    <label>What this office does<input name="description" maxlength="255" value="<?= e($o['description']) ?>"></label>
    <div class="cols">
        <label>Location<input name="location" maxlength="160" value="<?= e($o['location']) ?>"></label>
        <label>Floor<input name="floor" maxlength="40" value="<?= e($o['floor']) ?>"><span class="hint">Used to group offices on the Directions page.</span></label>
    </div>
    <label>Map spot
        <select name="map_spot">
            <option value="">Not shown on the map</option>
            <?php foreach (floor_spots() as $k => $sp): ?>
                <option value="<?= e($k) ?>" <?= ($o['map_spot'] ?? '') === $k ? 'selected' : '' ?>><?= e($sp['label']) ?> (<?= e(floor_names()[$sp['floor']]) ?>)</option>
            <?php endforeach; ?>
        </select>
        <span class="hint">Where this office appears on the indoor map and where the arrows lead. Edit the layout in includes/floorplan.php.</span>
    </label>
    <label>How to find the office inside the building<textarea name="directions" rows="3"><?= e((string)$o['directions']) ?></textarea></label>
    <div class="cols">
        <label>Phone<input name="phone" maxlength="60" value="<?= e($o['phone']) ?>"></label>
        <label>Email<input type="email" name="email" maxlength="120" value="<?= e($o['email']) ?>"></label>
    </div>
    <fieldset style="border:1px solid var(--line);border-radius:8px;margin:12px 0">
        <legend>Operating hours</legend>
        <div class="inline">
            <?php foreach ($dayNames as $n => $label): ?>
                <label><input type="checkbox" name="open_days[]" value="<?= $n ?>" <?= in_array((string)$n, $selDays, true) ? 'checked' : '' ?>> <?= $label ?></label>
            <?php endforeach; ?>
        </div>
        <div class="cols">
            <label>Opens<input type="time" name="open_time" value="<?= e(substr((string)$o['open_time'], 0, 5)) ?>"></label>
            <label>Closes<input type="time" name="close_time" value="<?= e(substr((string)$o['close_time'], 0, 5)) ?>"></label>
        </div>
        <label>Hours note<input name="hours_note" maxlength="255" value="<?= e($o['hours_note']) ?>"><span class="hint">Optional, e.g. lunch break or cut-off times.</span></label>
    </fieldset>
    <div class="actions">
        <button class="btn" type="submit">Save</button>
        <a class="btn ghost" href="<?= e(url('admin/offices.php')) ?>">Cancel</a>
    </div>
</form>
<?php admin_foot();