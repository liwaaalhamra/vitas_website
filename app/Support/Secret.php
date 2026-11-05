<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

/**
 * Gateway / banking secrets stored in .env encrypted with the app key
 * (AES-256-CBC). Encrypted values carry an "enc:" prefix; plain values are
 * returned unchanged so a secret can be rotated in before it is encrypted.
 *
 *   php artisan secrets:encrypt   -> prints the "enc:..." value to paste into .env
 */
class Secret
{
    const PREFIX = 'enc:';

    public static function encrypt(string $value): string
    {
        return self::PREFIX.Crypt::encryptString($value);
    }

    public static function reveal(?string $value): ?string
    {
        if ($value === null || $value === '' || strpos($value, self::PREFIX) !== 0) {
            return $value;
        }

        try {
            return Crypt::decryptString(substr($value, strlen(self::PREFIX)));
        } catch (DecryptException $e) {
            // encrypted with another APP_KEY (e.g. copied from a different server)
            throw new RuntimeException('Unable to decrypt a configured secret, re-encrypt it with this server\'s APP_KEY');
        }
    }

    public static function isEncrypted(?string $value): bool
    {
        return is_string($value) && strpos($value, self::PREFIX) === 0;
    }
}
