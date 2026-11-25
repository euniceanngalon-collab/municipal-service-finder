<?php
declare(strict_types=1);

/**
 * Indoor map of the municipal hall (schematic, not to scale).
 * Edit the coordinates below to match your real building.
 * Each "spot" is a place an office can be assigned to in Admin > Offices > Map spot.
 * ViewBox of every floor: 0 0 820 520. Main entrance is at (320, 440).
 */
function floor_names(): array
{
    return ['ground' => 'Ground Floor', 'second' => '2nd Floor'];
}

/** Rooms drawn on each floor. 'spot' rooms take the office name assigned to that spot. */
function floor_rooms(): array
{
    return [
        'ground' => [
            ['id' => 'treasurer', 'spot' => 'treasurer', 'x' => 60,  'y' => 60,  'w' => 200, 'h' => 210, 'label' => 'Treasurer'],
            ['id' => 'bplo',      'spot' => 'bplo',      'x' => 60,  'y' => 290, 'w' => 200, 'h' => 120, 'label' => 'Business Permits'],
            ['id' => 'civil',     'spot' => 'civil',     'x' => 380, 'y' => 60,  'w' => 200, 'h' => 120, 'label' => 'Civil Registrar'],
            ['id' => 'g-other',   'spot' => '',          'x' => 380, 'y' => 190, 'w' => 200, 'h' => 110, 'label' => 'Other offices'],
            ['id' => 'pao',       'spot' => 'pao',       'x' => 400, 'y' => 335, 'w' => 90,  'h' => 40,  'label' => 'Info Desk'],
            ['id' => 'stairs-g',  'spot' => '',          'x' => 510, 'y' => 340, 'w' => 70,  'h' => 80,  'label' => 'Stairs to 2F', 'kind' => 'stairs'],
            ['id' => 'mho',       'spot' => 'mho',       'x' => 650, 'y' => 190, 'w' => 140, 'h' => 170, 'label' => 'Health Center', 'kind' => 'outside'],
        ],
        'second' => [
            ['id' => 'assessor',    'spot' => 'assessor',    'x' => 60,  'y' => 60,  'w' => 200, 'h' => 120, 'label' => 'Assessor'],
            ['id' => 's-other-l',   'spot' => '',            'x' => 60,  'y' => 190, 'w' => 200, 'h' => 220, 'label' => 'Other offices'],
            ['id' => 'engineering', 'spot' => 'engineering', 'x' => 380, 'y' => 60,  'w' => 200, 'h' => 85,  'label' => 'Engineering'],
            ['id' => 's-other-r',   'spot' => '',            'x' => 380, 'y' => 155, 'w' => 200, 'h' => 85,  'label' => 'Other offices'],
            ['id' => 'mswdo',       'spot' => 'mswdo',       'x' => 380, 'y' => 250, 'w' => 200, 'h' => 90,  'label' => 'Social Welfare'],
            ['id' => 'stairs-s',    'spot' => '',            'x' => 510, 'y' => 340, 'w' => 70,  'h' => 80,  'label' => 'Stairs to 1F', 'kind' => 'stairs'],
        ],
    ];
}

/** Route (waypoints per floor) and plain-language steps for each spot. */
function floor_spots(): array
{
    $upGround = [[320, 440], [320, 410], [530, 410]];
    return [
        'pao' => [
            'label' => 'Public Assistance / Information Desk',
            'floor' => 'ground',
            'segments' => ['ground' => [[320, 440], [320, 355], [395, 355]]],
            'steps' => ['Enter through the main entrance.', 'The Information Desk is the first desk on your right.'],
        ],
        'bplo' => [
            'label' => 'Business Permits windows',
            'floor' => 'ground',
            'segments' => ['ground' => [[320, 440], [320, 350], [255, 350]]],
            'steps' => ['Enter through the main entrance.', 'Walk a few steps forward, then turn left.', 'The business permit windows are on your left.'],
        ],
        'treasurer' => [
            'label' => 'Treasurer / payment windows',
            'floor' => 'ground',
            'segments' => ['ground' => [[320, 440], [320, 170], [255, 170]]],
            'steps' => ['Enter through the main entrance.', 'Walk straight down the main hallway.', 'The payment windows are on your left.'],
        ],
        'civil' => [
            'label' => 'Civil Registrar',
            'floor' => 'ground',
            'segments' => ['ground' => [[320, 440], [320, 120], [385, 120]]],
            'steps' => ['Enter through the main entrance.', 'Walk straight to the end of the hallway.', 'The Civil Registrar is the last door on your right.'],
        ],
        'mho' => [
            'label' => 'Health Center (separate building)',
            'floor' => 'ground',
            'segments' => ['ground' => [[320, 440], [320, 480], [720, 480], [720, 365]]],
            'steps' => ['Go out to the front of the Municipal Hall.', 'Walk to the right, past the end of the building.', 'The Health Center is the separate building on the right. Register at the front desk.'],
        ],
        'mswdo' => [
            'label' => 'Social Welfare (2nd floor)',
            'floor' => 'second',
            'segments' => [
                'ground' => $upGround,
                'second' => [[530, 410], [320, 410], [320, 295], [375, 295]],
            ],
            'steps' => ['Enter through the main entrance and go to the stairs at the right side of the lobby.', 'Go up to the 2nd floor.', 'Walk to the main hallway, turn right, and take the first door on your right.'],
        ],
        'assessor' => [
            'label' => 'Assessor (2nd floor)',
            'floor' => 'second',
            'segments' => [
                'ground' => $upGround,
                'second' => [[530, 410], [320, 410], [320, 120], [255, 120]],
            ],
            'steps' => ['Enter through the main entrance and go to the stairs at the right side of the lobby.', 'Go up to the 2nd floor.', 'Walk down the hallway and turn left at the end.'],
        ],
        'engineering' => [
            'label' => 'Engineering (2nd floor)',
            'floor' => 'second',
            'segments' => [
                'ground' => $upGround,
                'second' => [[530, 410], [320, 410], [320, 100], [375, 100]],
            ],
            'steps' => ['Enter through the main entrance and go to the stairs at the right side of the lobby.', 'Go up to the 2nd floor.', 'Walk down the hallway; the office is the third door on your right.'],
        ],
    ];
}

/** Which spot a sample office belongs to, guessed from its name (used by installer and auto-upgrade). */
function guess_spot(string $name): string
{
    $n = mb_strtolower($name);
    $map = [
        'bplo' => 'bplo', 'business permit' => 'bplo',
        'civil registr' => 'civil',
        'social welfare' => 'mswdo', 'mswdo' => 'mswdo',
        'health' => 'mho',
        'treasur' => 'treasurer',
        'assessor' => 'assessor',
        'engineering' => 'engineering',
        'public assistance' => 'pao', 'mayor' => 'pao',
    ];
    foreach ($map as $needle => $spot) {
        if (str_contains($n, $needle)) {
            return $spot;
        }
    }
    return '';
}

/** Split a name into up to 3 short lines for SVG text. */
function wrap_label(string $s, int $max): array
{
    $words = preg_split('/\s+/u', trim($s), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $lines = [];
    $cur = '';
    foreach ($words as $w) {
        if ($cur !== '' && mb_strlen($cur . ' ' . $w) > $max) {
            $lines[] = $cur;
            $cur = $w;
        } else {
            $cur = $cur === '' ? $w : $cur . ' ' . $w;
        }
    }
    if ($cur !== '') {
        $lines[] = $cur;
    }
    if (count($lines) > 3) {
        $lines = array_slice($lines, 0, 3);
        $lines[2] = rtrim(mb_substr($lines[2], 0, max(1, $max - 1))) . '…';
    }
    return $lines;
}

/** Render one floor as inline SVG. $officeBySpot maps spot key => office row. */
function render_floor_svg(string $floorKey, array $officeBySpot): void
{
    $rooms = floor_rooms()[$floorKey] ?? [];
    ?>
    <svg class="plan-svg" data-floor="<?= e($floorKey) ?>" viewBox="0 0 820 520" role="img" aria-label="<?= e(floor_names()[$floorKey]) ?> floor plan">
        <rect x="0" y="0" width="820" height="520" class="plan-bg"/>
        <rect x="40" y="40" width="560" height="400" rx="6" class="plan-building"/>
        <rect x="260" y="60" width="120" height="380" class="plan-hall"/>
        <rect x="260" y="320" width="340" height="120" class="plan-hall"/>
        <?php foreach ($rooms as $r):
            $kind = $r['kind'] ?? 'room';
            $office = $r['spot'] !== '' ? ($officeBySpot[$r['spot']] ?? null) : null;
            $label = $office ? $office['name'] : $r['label'];
            $lines = wrap_label($label, $r['w'] < 100 ? 12 : ($r['w'] < 150 ? 16 : 24));
            $cx = $r['x'] + $r['w'] / 2;
            $cy = $r['y'] + $r['h'] / 2 - (count($lines) - 1) * 9;
            ?>
            <g class="plan-room <?= e($kind) ?><?= $office ? ' has-office' : '' ?>" data-room="<?= e($r['id']) ?>"<?= $r['spot'] !== '' ? ' data-spot="' . e($r['spot']) . '"' : '' ?>>
                <rect x="<?= $r['x'] ?>" y="<?= $r['y'] ?>" width="<?= $r['w'] ?>" height="<?= $r['h'] ?>" rx="4"/>
                <text x="<?= $cx ?>" y="<?= $cy ?>" text-anchor="middle">
                    <?php foreach ($lines as $i => $ln): ?><tspan x="<?= $cx ?>" dy="<?= $i === 0 ? 0 : 18 ?>"><?= e($ln) ?></tspan><?php endforeach; ?>
                </text>
            </g>
        <?php endforeach; ?>
        <?php if ($floorKey === 'ground'): ?>
            <rect x="290" y="436" width="60" height="8" class="plan-door"/>
            <text x="320" y="504" text-anchor="middle" class="plan-note">MAIN ENTRANCE</text>
            <g class="you-are-here"><circle cx="320" cy="440" r="9"/><circle cx="320" cy="440" r="16" class="pulse"/></g>
        <?php endif; ?>
        <g class="route-layer" data-floor="<?= e($floorKey) ?>"></g>
    </svg>
    <?php
}