<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/includes/layout.php';

$cats = db()->query(
    'SELECT c.*, COUNT(s.id) AS total FROM categories c
     LEFT JOIN services s ON s.category_id = c.id AND s.is_active = 1
     GROUP BY c.id ORDER BY c.sort_order'
)->fetchAll();

$featured = db()->query(
    'SELECT s.*, c.name AS category_name, c.icon AS category_icon, o.name AS office_name
       FROM services s JOIN categories c ON c.id = s.category_id JOIN offices o ON o.id = s.office_id
      WHERE s.is_active = 1 AND s.featured = 1 ORDER BY s.title LIMIT 6'
)->fetchAll();

page_head('Find the right service', 'home');
?>
<section class="hero">
    <h1>Find the right municipal service, office, and requirements.</h1>
    <p>Describe what you need in your own words, check the documents to bring, and follow the steps.</p>
    <form class="searchbar" action="<?= e(url('services.php')) ?>" method="get" role="search">
        <label for="q" class="sr">Search</label>
        <input id="q" name="q" type="search" placeholder="e.g. gusto ko mag-renew sa negosyo / need a birth certificate" autocomplete="off" required>
        <button class="btn" type="submit">Search</button>
    </form>
</section>

<?php disclaimer(); ?>

<section>
    <h2>How to find a service</h2>
    <div class="grid three">
        <div class="card step"><span class="num">1</span><p>Search or pick a category for the service you need.</p></div>
        <div class="card step"><span class="num">2</span><p>Open the service to see the office, requirements, fees and steps.</p></div>
        <div class="card step"><span class="num">3</span><p>Prepare the documents, then visit or contact the office.</p></div>
    </div>
</section>

<section>
    <h2>Popular categories</h2>
    <div class="grid cats">
        <?php foreach ($cats as $c): ?>
            <a class="card cat" href="<?= e(url('services.php?cat=' . $c['slug'])) ?>">
                <span class="cat-icon"><?= e($c['icon']) ?></span>
                <strong><?= e($c['name']) ?></strong>
                <small class="muted"><?= e($c['description']) ?></small>
                <small class="count"><?= (int)$c['total'] ?> service<?= (int)$c['total'] === 1 ? '' : 's' ?></small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <div class="row-between"><h2>Featured services</h2><a href="<?= e(url('services.php')) ?>">See all</a></div>
    <div class="grid two">
        <?php foreach ($featured as $s) { service_card($s); } ?>
    </div>
</section>

<section>
    <h2>Quick links</h2>
    <div class="grid three">
        <a class="card quick" href="<?= e(url('services.php')) ?>"><strong>▦ All Services</strong><small class="muted">Browse the full service catalog.</small></a>
        <a class="card quick" href="<?= e(url('offices.php')) ?>"><strong>🏛 Office Directory</strong><small class="muted">Contacts, hours and locations.</small></a>
        <a class="card quick" href="<?= e(url('directions.php')) ?>"><strong>🧭 How to Get Assistance</strong><small class="muted">Find your way inside the municipal hall.</small></a>
    </div>
</section>
<?php page_foot('home');
