<?php

namespace App\Console\Commands;

use App\Support\Secret;
use Illuminate\Console\Command;

/**
 * Encrypts a gateway / banking secret for .env, e.g.
 *   AREEBA_API_PASSWORD="enc:eyJpdiI6..."
 * The value is asked for interactively so it never lands in shell history.
 * Must be run on the server that will use it (encryption is tied to APP_KEY).
 */
class EncryptSecret extends Command
{
    protected $signature = 'secrets:encrypt';

    protected $description = 'Encrypt a secret (AES-256) for use in .env';

    public function handle(): int
    {
        $value = (string) $this->secret('Secret value');

        if ($value === '') {
            $this->error('Nothing to encrypt.');

            return self::FAILURE;
        }

        $this->line('');
        $this->line('Paste this into .env (keep the quotes):');
        $this->line('"'.Secret::encrypt($value).'"');

        return self::SUCCESS;
    }
}
