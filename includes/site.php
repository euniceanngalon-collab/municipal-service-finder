<?php
declare(strict_types=1);

function page_head(string $title, string $active = ''): void
{
    $navItems = [
        'home' => ['Home', 'index.php'],
        'services' => ['Services', 'services.php'],
        'offices' => ['Offices', 'offices.php'],
        'directions' => ['Directions', 'directions.php'],
    ];

    $pageTitle = $title . ' | ' . APP_NAME;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0b2447">
        <title><?= e($pageTitle) ?></title>
        <link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>">
    </head>
    <body>
    <header class="topbar">
        <div class="container bar">
            <a class="brand" href="<?= e(url('index.php')) ?>">
                <span class="logo">MSF</span>
                <span><?= e(APP_NAME) ?></span>
            </a>

            <nav class="topnav" aria-label="Main navigation">
                <?php foreach ($navItems as $key => [$label, $href]): ?>
                    <a href="<?= e(url($href)) ?>"
                       <?= $active === $key ? 'class="on" aria-current="page"' : '' ?>>
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>

    <main class="container page">
    <?php
}

function page_foot(string $active = ''): void
{
    $navItems = [
        'home' => ['⌂', 'Home', 'index.php'],
        'services' => ['▤', 'Services', 'services.php'],
        'offices' => ['⌖', 'Offices', 'offices.php'],
        'directions' => ['➜', 'Directions', 'directions.php'],
    ];
    ?>
    </main>

    <footer class="container footer">
        <p>
            <?= e(APP_NAME) ?> ·
            <?= e(lgu_full_name()) ?>
        </p>
    </footer>

    <nav class="bottomnav" aria-label="Mobile navigation">
        <?php foreach ($navItems as $key => [$icon, $label, $href]): ?>
            <a href="<?= e(url($href)) ?>"
               <?= $active === $key ? 'class="on" aria-current="page"' : '' ?>>
                <span><?= e($icon) ?></span>
                <?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <script src="<?= e(url('assets/app.js')) ?>" defer></script>
    </body>
    </html>
    <?php
} 

function disclaimer(): void
{
    if (lgu('show_notice') !== '1') {
        return;
    }

    echo '<div class="alert warn" role="note">';
    echo '<strong>Important notice:</strong> ';
    echo 'The information displayed on this website is for reference only. ';
    echo 'Some service details may still be sample data or may be out of date. ';
    echo 'Please verify the requirements, fees, schedules, and office details ';
    echo 'with the concerned municipal office before proceeding.';
    echo '</div>';
}

function office_badge(array $o): string
{
    $status = office_status($o);

    if ($status['open'] === true) {
        $class = 'open';
    } elseif ($status['open'] === false) {
        $class = 'closed';
    } else {
        $class = 'neutral';
    }

    return '<span class="badge ' . $class . '">' .
        e($status['label']) .
        '</span>';
}

function service_card(array $s): void
{
    $serviceId = (int)($s['id'] ?? 0);
    $title = (string)($s['title'] ?? 'Untitled service');
    $category = (string)($s['category_name'] ?? '');
    $icon = (string)($s['category_icon'] ?? '');
    $summary = (string)($s['summary'] ?? '');
    $office = (string)($s['office_name'] ?? '');

    ?>
    <article class="card service">
        <span class="chip"><?= e($icon) ?> <?= e($category) ?></span>

        <h3>
            <a href="<?= e(url('service.php?id=' . $serviceId)) ?>">
                <?= e($title) ?>
            </a>
        </h3>

        <?php if ($summary !== ''): ?>
            <p><?= e($summary) ?></p>
        <?php endif; ?>

        <ul class="meta">
            <?php if ($office !== ''): ?>
                <li>Office: <?= e($office) ?></li>
            <?php endif; ?>
            <?php if (!empty($s['fees'])): ?>
                <li>Fees: <?= e((string)$s['fees']) ?></li>
            <?php endif; ?>
            <?php if (!empty($s['processing_time'])): ?>
                <li>Processing time: <?= e((string)$s['processing_time']) ?></li>
            <?php endif; ?>
        </ul>

        <?php if (is_stale($s['last_verified'] ?? null)): ?>
            <p class="small warn-text">May be out of date</p>
        <?php endif; ?>

        <a class="btn small" href="<?= e(url('service.php?id=' . $serviceId)) ?>">
            View requirements →
        </a>
    </article>
    <?php
}