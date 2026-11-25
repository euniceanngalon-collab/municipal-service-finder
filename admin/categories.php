<?php
declare(strict_types=1);
require __DIR__ . '/_admin.php';
require_admin();

function slugify(string $s): string
{
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s), '-'));
    return $s !== '' ? $s : 'category-' . bin2hex(random_bytes(2));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $name = trim((string)($_POST['name'] ?? ''));
    $icon = trim((string)($_POST['icon'] ?? '')) ?: '📄';
    $desc = trim((string)($_POST['description'] ?? ''));
    $order = (int)($_POST['sort_order'] ?? 0);
    try {
        if ($action === 'delete') {
            db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            flash('Category deleted.');
        } elseif ($name === '' || mb_strlen($name) > 80) {
            flash('Category name is required (80 characters max).', 'error');
        } elseif ($action === 'add') {
            db()->prepare('INSERT INTO categories (name, slug, icon, description, sort_order) VALUES (?,?,?,?,?)')
                ->execute([$name, slugify($name), mb_substr($icon, 0, 4), mb_substr($desc, 0, 255), $order]);
            flash('Category added.');
        } elseif ($action === 'save') {
            db()->prepare('UPDATE categories SET name = ?, icon = ?, description = ?, sort_order = ? WHERE id = ?')
                ->execute([$name, mb_substr($icon, 0, 4), mb_substr($desc, 0, 255), $order, $id]);
            flash('Category updated.');
        }
    } catch (PDOException $ex) {
        flash($action === 'delete' ? 'Cannot delete: this category still has services.' : 'Could not save (is the name already used?).', 'error');
    }
    redirect('admin/categories.php');
}

$rows = db()->query('SELECT c.*, (SELECT COUNT(*) FROM services s WHERE s.category_id = c.id) AS total FROM categories c ORDER BY sort_order, name')->fetchAll();

admin_head('Manage categories', 'categories');
?>
<h1>Categories</h1>
<p class="muted">Clear names and descriptions help residents choose the right category.</p>

<?php foreach ($rows as $r): ?>
    <form method="post" class="form card pad" style="margin-bottom:10px">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <div class="cols">
            <label>Name<input name="name" maxlength="80" required value="<?= e($r['name']) ?>"></label>
            <label>Icon (emoji)<input name="icon" maxlength="4" value="<?= e($r['icon']) ?>"></label>
        </div>
        <label>Description<input name="description" maxlength="255" value="<?= e($r['description']) ?>"></label>
        <div class="cols">
            <label>Order<input name="sort_order" type="text" inputmode="numeric" value="<?= (int)$r['sort_order'] ?>"></label>
            <div><span class="muted small"><?= (int)$r['total'] ?> service(s)</span></div>
        </div>
        <div class="actions">
            <button class="btn small" name="action" value="save" type="submit">Save</button>
            <button class="btn small danger" name="action" value="delete" type="submit" onclick="return confirm('Delete this category?')">Delete</button>
        </div>
    </form>
<?php endforeach; ?>

<form method="post" class="form card pad">
    <h2 style="margin-top:0">Add category</h2>
    <?= csrf_field() ?><input type="hidden" name="action" value="add">
    <div class="cols">
        <label>Name<input name="name" maxlength="80" required></label>
        <label>Icon (emoji)<input name="icon" maxlength="4" placeholder="📄"></label>
    </div>
    <label>Description<input name="description" maxlength="255"></label>
    <label>Order<input name="sort_order" type="text" inputmode="numeric" value="<?= count($rows) ?>"></label>
    <button class="btn" type="submit">Add category</button>
</form>
<?php admin_foot();
