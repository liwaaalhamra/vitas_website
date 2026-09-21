<?php

namespace App\Services\Areeba;

use RuntimeException;

/**
 * Thrown when the Areeba gateway cannot be reached or answers with something
 * we cannot use (transport error, non-JSON body, success:false on init).
 */
class AreebaException extends RuntimeException
{
    public array $context = [];

    public static function make(string $message, array $context = [], int $code = 0): self
    {
        $e = new self($message, $code);
        $e->context = $context;

        return $e;
    }
}
