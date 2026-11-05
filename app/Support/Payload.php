<?php

namespace App\Support;

/**
 * Lookups in third-party JSON whose field casing / nesting is not guaranteed.
 */
class Payload
{
    /**
     * Value of the first of $keys (in priority order, case-insensitive) found at any depth.
     */
    public static function find($data, array $keys)
    {
        foreach ($keys as $key) {
            if (($found = self::findKey($data, strtolower($key))) !== null) {
                return $found;
            }
        }

        return null;
    }

    private static function findKey($data, string $key)
    {
        if (!is_array($data)) {
            return null;
        }
        foreach ($data as $k => $value) {
            if (is_string($k) && strtolower($k) === $key && $value !== null && $value !== '' && !is_array($value)) {
                return $value;
            }
        }
        foreach ($data as $value) {
            if (is_array($value) && ($found = self::findKey($value, $key)) !== null) {
                return $found;
            }
        }

        return null;
    }
}
