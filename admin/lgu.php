<?php
declare(strict_types=1);
require __DIR__ . '/_admin.php';
require_admin();

$errors = [];
$fields = ['name', 'province', 'hall_address', 'hotline', 'email', 'facebook', 'lat', 'lng'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $v = [];
    foreach ($fields as $k) {
        $v[$k] = trim((string)($_POST[$k] ?? ''));
    }
    $v['show_notice'] = isset($_POST['show_notice']) ? '1' : '0';
    if ($v['name'] === '') { $errors[] = 'LGU name is required.'; }
    if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email address is not valid.'; }
    if ($v['facebook'] !== '' && !preg_match('#^https?://#i', $v['facebook'])) { $errors[] = 'Facebook link must start with http:// or https://'; }
    foreach (['lat' => [-90, 90], 'lng' => [-180, 180]] as $k => [$lo, $hi]) {
        if ($v[$k] !== '' && (!is_numeric($v[$k]) || (float)$v[$k] < $lo || (float)$v[$k] > $hi)) {
            $errors[] = ($k === 'lat' ? 'Latitude' : 'Longitude') . ' must be a number between ' . $lo . ' and ' . $hi . '.';
        }
    }
    if (($v['lat'] === '') !== ($v['lng'] === '')) { $errors[] = 'Fill in both latitude and longitude, or leave both empty.'; }
    if (!$errors) {
        $st = db()->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)');
        foreach ($v as $k => $val) {
            $st->execute([$k, $val]);
        }
        flash('LGU settings saved.');
        redirect('admin/lgu.php');
    }
}

$cur = [];
foreach (array_merge($fields, ['show_notice']) as $k) {
    $cur[$k] = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST[$k] ?? '') : lgu($k);
}

admin_head('LGU Settings', 'lgu');
?>
<h1>LGU settings</h1>
<p class="muted">This installation serves one local government unit. These details appear in the header, footer and Directions page.</p>
<?php foreach ($errors as $er): ?><div class="alert err" role="alert"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" class="form card pad">
    <?= csrf_field() ?>
    <div class="cols">
        <label>LGU name *<input name="name" maxlength="80" required value="<?= e((string)$cur['name']) ?>"><span class="hint">e.g. Municipality of Buenavista</span></label>
        <label>Province<input name="province" maxlength="80" value="<?= e((string)$cur['province']) ?>"></label>
    </div>
    <label>Municipal hall address<input name="hall_address" maxlength="255" value="<?= e((string)$cur['hall_address']) ?>"></label>
    <div class="cols">
        <label>Hotline / trunkline<input name="hotline" maxlength="60" value="<?= e((string)$cur['hotline']) ?>"></label>
        <label>Official email<input type="email" name="email" maxlength="120" value="<?= e((string)$cur['email']) ?>"></label>
    </div>
    <label>Official Facebook page (optional)<input name="facebook" maxlength="255" value="<?= e((string)$cur['facebook']) ?>"></label>
    <div class="cols">
        <label>Hall latitude<input name="lat" inputmode="decimal" value="<?= e((string)$cur['lat']) ?>"><span class="hint">Right-click the hall in Google Maps and copy the numbers, e.g. 8.9475 and 125.5406</span></label>
        <label>Hall longitude<input name="lng" inputmode="decimal" value="<?= e((string)$cur['lng']) ?>"></label>
    </div>
    <label class="inline"><span><input type="checkbox" name="show_notice" value="1" <?= (string)$cur['show_notice'] === '1' ? 'checked' : '' ?>> Show the "sample data / please confirm with the office" notice</span></label>
    <span class="hint">Turn this off only after the LGU has verified every service and office.</span>
    <div class="actions"><button class="btn" type="submit">Save</button></div>
</form>
<?php admin_foot();