<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Check whether a staff account has a valid active session.
 */
function is_staff(): bool
{
    return !empty($_SESSION['staff_id']);
}

/**
 * Load the current staff account from the database.
 * This checks the account's current status and office assignment,
 * instead of trusting session values alone.
 */
function current_staff(): ?array
{
    if (!is_staff()) {
        return null;
    }

    static $staff = false;

    if ($staff !== false) {
        return $staff;
    }

    $stmt = db()->prepare(
        'SELECT id, full_name, username, office_id, is_active
           FROM staff
          WHERE id = ?
          LIMIT 1'
    );
    $stmt->execute([(int)$_SESSION['staff_id']]);
    $row = $stmt->fetch();

    if (
        !$row ||
        (int)$row['is_active'] !== 1 ||
        empty($row['office_id'])
    ) {
        staff_logout_session();
        return null;
    }

    $office = db()->prepare(
        'SELECT id, name FROM offices WHERE id = ? LIMIT 1'
    );
    $office->execute([(int)$row['office_id']]);

    if (!$office->fetch()) {
        staff_logout_session();
        return null;
    }

    $staff = $row;
    return $staff;
}

/**
 * Clear only the staff login session.
 */
function staff_logout_session(): void
{
    unset(
        $_SESSION['staff_id'],
        $_SESSION['staff_name'],
        $_SESSION['staff_office_id']
    );
}

/**
 * Require an authenticated, active staff account.
 */
function require_staff(): array
{
    $staff = current_staff();

    if (!$staff) {
        redirect('staff/login.php');
    }

    return $staff;
}

/**
 * Confirm that a staff member is assigned to the requested office.
 */
function staff_can_access_office(int $officeId): bool
{
    $staff = require_staff();

    return (int)$staff['office_id'] === $officeId;
}

/**
 * Stop access if a staff member tries to open another office's record.
 */
function require_staff_office(int $officeId): array
{
    $staff = require_staff();

    if ($officeId <= 0 || (int)$staff['office_id'] !== $officeId) {
        http_response_code(403);
        exit('Access denied. You can only manage your assigned office.');
    }

    return $staff;
}

