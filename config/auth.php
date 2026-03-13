<?php
/**
 * Session and authorization helpers.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Force users to log in before loading private pages.
 */
function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit();
    }
}

/**
 * Restrict page access by role.
 */
function require_role(array $roles): void
{
    require_login();

    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        echo 'Access denied.';
        exit();
    }
}

/**
 * Basic input cleaner for search/date filters.
 */
function clean_input(?string $value): string
{
    return trim((string) filter_var($value ?? '', FILTER_SANITIZE_SPECIAL_CHARS));
}