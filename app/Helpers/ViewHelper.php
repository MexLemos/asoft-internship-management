<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Escapes HTML characters for XSS prevention.
 */
function e(?string $value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Returns the base asset URL.
 */
function asset(string $path): string
{
    $baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
    $path = ltrim($path, '/');
    return "{$baseUrl}/assets/{$path}";
}

/**
 * Generates an application URL.
 */
function url(string $path = ''): string
{
    $baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
    $path = ltrim($path, '/');
    return $path === '' ? $baseUrl : "{$baseUrl}/{$path}";
}

/**
 * Generates a CSRF hidden input field for forms.
 */
function csrf_field(): string
{
    $token = \App\Core\CSRF::getToken();
    return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
}

/**
 * Returns current CSRF token string.
 */
function csrf_token(): string
{
    return \App\Core\CSRF::getToken();
}

/**
 * Returns currently authenticated user or null.
 */
function auth_user(): ?array
{
    return \App\Core\Session::get('user');
}

/**
 * Checks if current user is logged in.
 */
function auth_check(): bool
{
    return \App\Core\Session::has('user');
}

/**
 * Checks if current user has a specific role.
 */
function has_role(string $role): bool
{
    $user = auth_user();
    if (!$user) {
        return false;
    }
    return in_array($role, $user['roles'] ?? [], true);
}

/**
 * Checks if current user has a specific permission.
 */
function has_permission(string $permission): bool
{
    $user = auth_user();
    if (!$user) {
        return false;
    }
    if (in_array('super_admin', $user['roles'] ?? [], true)) {
        return true;
    }
    return in_array($permission, $user['permissions'] ?? [], true);
}

/**
 * Retrieves flash messages from session.
 */
function flash(string $key): ?string
{
    return \App\Core\Session::flash($key);
}

/**
 * Formats a date string into readable format (d/m/Y or d/m/Y H:i).
 */
function format_date(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '-';
    }
    $ts = strtotime($date);
    if (!$ts) {
        return $date;
    }
    return date($withTime ? 'd/m/Y H:i' : 'd/m/Y', $ts);
}

/**
 * Formats currency / numbers.
 */
function format_number(float|int|null $num, int $decimals = 2): string
{
    return number_format((float)($num ?? 0), $decimals, ',', '.');
}
