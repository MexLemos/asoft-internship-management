<?php

declare(strict_types=1);

namespace App\Core;

class CSRF
{
    private const SESSION_KEY = '_csrf_token';

    /**
     * Gets or generates current CSRF token.
     */
    public static function getToken(): string
    {
        Session::start();
        $token = Session::get(self::SESSION_KEY);
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }
        return $token;
    }

    /**
     * Validates a provided token against the session token.
     */
    public static function validate(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        $sessionToken = Session::get(self::SESSION_KEY);
        if (!$sessionToken) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    /**
     * Regenerates the CSRF token.
     */
    public static function regenerate(): string
    {
        Session::start();
        $token = bin2hex(random_bytes(32));
        Session::set(self::SESSION_KEY, $token);
        return $token;
    }
}
