<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Validates Angolan Bilhete de Identidade format (usually 9 digits + 2 letters + 3 digits, total 14 chars, e.g. 000123456LA012).
 */
function is_valid_angolan_bi(string $bi): bool
{
    $bi = strtoupper(trim($bi));
    // Standard format: 9 digits followed by 2 letters and 3 digits
    return (bool)preg_match('/^[0-9]{9}[A-Z]{2}[0-9]{3}$/', $bi);
}

/**
 * Validates Angolan Tax Identification Number (NIF).
 */
function is_valid_nif(string $nif): bool
{
    $nif = trim($nif);
    // Typical NIF in Angola is 10 digits or alphanumeric for institutions
    return strlen($nif) >= 9 && strlen($nif) <= 14;
}

/**
 * Validates phone numbers (supports Angolan formats +2449xxxxxxxx or 9xxxxxxxx).
 */
function is_valid_phone(string $phone): bool
{
    $phone = preg_replace('/[^\d+]/', '', $phone);
    return (bool)preg_match('/^(\+244)?[9][1-9][0-9]{7}$/', $phone);
}
