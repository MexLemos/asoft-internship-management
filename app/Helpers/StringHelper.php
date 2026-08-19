<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Generates a URL-friendly slug from string.
 */
function slugify(string $text): string
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // trim
    $text = trim($text, '-');
    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    // lowercase
    $text = strtolower($text);

    return empty($text) ? 'n-a' : $text;
}

/**
 * Generates a unique secure token (UUID v4 or random hex).
 */
function generate_token(int $length = 32): string
{
    return bin2hex(random_bytes((int)($length / 2)));
}

/**
 * Truncates text with ellipsis.
 */
function truncate_text(string $text, int $limit = 100): string
{
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return mb_substr($text, 0, $limit) . '...';
}
