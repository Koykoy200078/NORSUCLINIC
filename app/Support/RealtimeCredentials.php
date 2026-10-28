<?php

namespace App\Support;

/**
 * The three values that tie the application, the Reverb server and the browsers together (REVERB_APP_ID / KEY / SECRET).
 *
 * `.env.example` leaves them empty and an empty key switched real-time off without a word ("Live updates off" on every
 * address, see App\Support\Realtime). A blank value is therefore filled in from this server's own APP_KEY, the same way for
 * the application (config/broadcasting.php) and for the Reverb server (config/reverb.php), so a fresh install needs no
 * set-up. A value written in .env always wins (a server that already has its own values keeps them).
 *
 * Both configs call this while they are loaded, with `env()` results; nothing here reads the environment itself, so it is
 * also correct when the configuration is cached. Without an APP_KEY nothing is invented: the key stays empty and real-time
 * stays off, rather than running with a secret anybody could guess.
 */
final class RealtimeCredentials
{
    public static function appId(?string $explicit): string
    {
        $explicit = trim((string) $explicit);

        return $explicit !== '' ? $explicit : 'clinic';
    }

    /** Public: every page hands it to the browser, as the Reverb key always is. */
    public static function key(?string $explicit, ?string $appKey): string
    {
        $explicit = trim((string) $explicit);

        return $explicit !== '' ? $explicit : substr(self::derive('reverb-app-key', $appKey), 0, 20);
    }

    /** Private: signs the notices and the private-channel logins; never sent to a browser. */
    public static function secret(?string $explicit, ?string $appKey): string
    {
        $explicit = trim((string) $explicit);

        return $explicit !== '' ? $explicit : self::derive('reverb-app-secret', $appKey);
    }

    private static function derive(string $label, ?string $appKey): string
    {
        $appKey = trim((string) $appKey);

        return $appKey === '' ? '' : hash_hmac('sha256', $label, $appKey);
    }
}
