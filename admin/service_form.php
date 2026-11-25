<?php
declare(strict_types=1);
require __DIR__ . '/_admin.php';
require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$cats = db()->query('SELECT id, name FROM categories ORDER BY sort_order')->fetchAll();
$offs = db()->query('SELECT id, name FROM offices ORDER BY name')->fetchAll();

$s = [
    'category_id' => $cats[0]['id'] ?? 0, 'office_id' => $offs[0]['id'] ?? 0, 'title' => '', 'summary' => '', 'description' => '',
    'who_can_apply' => '', 'requirements' => '', 'steps' => '', 'fees' => '', 'processing_time' => '', 'keywords' => '',
    'featured' => 0, 'is_active' => 1, 'last_verified' => date('Y-m-d'),
];
if ($id) {
    $st = db()->prepare('SELECT * FROM services WHERE id = ?');
    $st->execute([$id]);
    $found = $st->fetch();
    if (!$found) {
        flash('Service not found.', 'error');
        redirect('admin/services.php');
    }
    $s = $found;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $f = [
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'office_id' => (int)($_POST['office_id'] ?? 0),
        'title' => trim((string)($_POST['title'] ?? '')),
        'summary' => trim((string)($_POST['summary'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'who_can_apply' => trim((string)($_POST['who_can_apply'] ?? '')),
        'requirements' => implode("\n", lines($_POST['requirements'] ?? '')),
        'steps' => implode("\n", lines($_POST['steps'] ?? '')),
        'fees' => trim((string)($_POST['fees'] ?? '')),
        'processing_time' => trim((string)($_POST['processing_time'] ?? '')),
        'keywords' => trim((string)($_POST['keywords'] ?? '')),
        'featured' => isset($_POST['featured']) ? 1 : 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'last_verified' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['last_verified'] ?? '')) ? $_POST['last_verified'] : null,
    ];
    if ($f['title'] === '') { $errors[] = 'Title is required.'; }
    if (mb_strlen($f['title']) > 150) { $errors[] = 'Title is too long (150 characters max).'; }
    if (mb_strlen($f['summary']) > 255) { $errors[] = 'Summary is too long (255 characters max).'; }
    if (!$f['category_id'] || !$f['office_id']) { $errors[] = 'Choose a category and an office.'; }
    $s = array_merge($s, $f);

    if (!$errors) {
        $cols = array_keys($f);
        if ($id) {
            $sql = 'UPDATE services SET ' . implode(', ', array_map(fn($c) => "$c = :$c", $cols)) . ' WHERE id = :id';
            db()->prepare($sql)->execute($f + ['id' => $id]);
            flash('Service updated.');
        } else {
            $sql = 'INSERT INTO services (' . implode(',', $cols) . ') VALUES (' . implode(',', array_map(fn($c) => ":$c", $cols)) . ')';
            db()->prepare($sql)->execute($f);
            flash('Service added.');
        }
        redirect('admin/services.php');
    }
}

admin_head($id ? 'Edit service' : 'Add service', 'services');
?>
<h1><?= $id ? 'Edit service' : 'Add service' ?></h1>
<?php foreach ($errors as $er): ?><div class="alert err" role="alert"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" class="form card pad">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
    <label>Service title *<input name="title" maxlength="150" required value="<?= e($s['title']) ?>"></label>
    <div class="cols">
        <label>Category *
            <select name="category_id"><?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$s['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
        <label>Responsible office *
            <select name="office_id"><?php foreach ($offs as $o): ?><option value="<?= (int)$o['id'] ?>" <?= (int)$s['office_id'] === (int)$o['id'] ? 'selected' : '' ?>><?= e($o['name']) ?></option><?php endforeach; ?></select></label>
    </div>
    <label>Short summary<input name="summary" maxlength="255" value="<?= e($s['summary']) ?>"><span class="hint">One plain-language sentence shown on cards and search results.</span></label>
    <label>About this service<textarea name="description"><?= e($s['description']) ?></textarea></label>
    <label>Who can apply<input name="who_can_apply" maxlength="255" value="<?= e($s['who_can_apply']) ?>"></label>
    <label>Requirements<textarea name="requirements" rows="8"><?= e($s['requirements']) ?></textarea><span class="hint">One requirement per line. Shown as a checklist.</span></label>
    <label>Step-by-step procedure<textarea name="steps" rows="8"><?= e($s['steps']) ?></textarea><span class="hint">One step per line, in order.</span></label>
    <div class="cols">
        <label>Fees<input name="fees" maxlength="255" value="<?= e($s['fees']) ?>"></label>
        <label>Processing time<input name="processing_time" maxlength="120" value="<?= e($s['processing_time']) ?>"></label>
    </div>
    <label>Search keywords<input name="keywords" maxlength="500" value="<?= e($s['keywords']) ?>"><span class="hint">Words residents might type, including Bisaya/Tagalog (e.g. natawo, kasal, negosyo).</span></label>
    <div class="cols">
        <label>Last verified with the office<input type="date" name="last_verified" value="<?= e((string)$s['last_verified']) ?>"></label>
        <div class="inline">
            <label><input type="checkbox" name="is_active" <?= $s['is_active'] ? 'checked' : '' ?>> Visible to the public</label>
            <label><input type="checkbox" name="featured" <?= $s['featured'] ? 'checked' : '' ?>> Featured on home page</label>
        </div>
    </div>
    <div class="actions">
        <button class="btn" type="submit">Save</button>
        <a class="btn ghost" href="<?= e(url('admin/services.php')) ?>">Cancel</a>
    </div>
</form>
<?php admin_foot();
