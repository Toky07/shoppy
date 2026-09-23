<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Shared\Domain\Exception\RateLimited;

final class FileRateLimiter
{
    public function __construct(private string $directory)
    {
    }

    public function hit(string $bucket, string $identity, int $limit, int $windowSeconds): void
    {
        if ($limit <= 0 || $windowSeconds <= 0) {
            return;
        }

        if (!is_dir($this->directory) && !mkdir($this->directory, 0755, true) && !is_dir($this->directory)) {
            return;
        }

        $path = $this->directory.'/'.hash('sha256', $bucket.'|'.$identity);
        $handle = fopen($path, 'c+');

        if ($handle === false) {
            return;
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                return;
            }

            $raw = stream_get_contents($handle);
            $now = time();
            $threshold = $now - $windowSeconds;
            $hits = [];

            if (is_string($raw) && $raw !== '') {
                foreach (explode(',', $raw) as $stamp) {
                    if ($stamp !== '' && (int) $stamp > $threshold) {
                        $hits[] = (int) $stamp;
                    }
                }
            }

            if (count($hits) >= $limit) {
                throw new RateLimited();
            }

            $hits[] = $now;
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, implode(',', $hits));
            fflush($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
