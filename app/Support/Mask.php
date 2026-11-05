<?php

namespace App\Support;

/**
 * PII masking applied server side before anything reaches the browser.
 */
class Mask
{
    /**
     * "Mohammed Ali Hassan" -> "Mo****** A** H*****"
     * Keeps the first 2 letters of the first name and the first letter of the others.
     */
    public static function name(?string $name): ?string
    {
        $name = trim(preg_replace('/\s+/u', ' ', (string) $name));
        if ($name === '') {
            return null;
        }

        $parts = explode(' ', $name);
        foreach ($parts as $i => $part) {
            $length = mb_strlen($part);
            $keep = $i === 0 ? min(2, max(1, $length - 1)) : 1;
            $parts[$i] = mb_substr($part, 0, $keep).str_repeat('*', max(1, $length - $keep));
        }

        return implode(' ', $parts);
    }

    /**
     * "9647701234567" -> "*********4567"
     */
    public static function phone(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }

        $visible = min(4, max(0, strlen($digits) - 4));

        return str_repeat('*', strlen($digits) - $visible).substr($digits, -$visible ?: strlen($digits));
    }
}
