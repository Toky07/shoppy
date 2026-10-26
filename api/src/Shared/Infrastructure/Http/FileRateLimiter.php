<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Shared\Application\FailClosedRateLimit;
use App\Shared\Application\RateLimiter;
use App\Shared\Application\RateLimitWindow;
use App\Shared\Domain\Clock;
use RuntimeException;

final readonly class FileRateLimiter implements RateLimiter
{
    public function __construct(
        private string $directory,
        private Clock $clock,
    ) {
    }

    public function hit(string $bucket, string $identity, int $limit, int $windowSeconds): void
    {
        if ($limit <= 0 || $windowSeconds <= 0) {
            return;
        }

        FailClosedRateLimit::run(function () use ($bucket, $identity, $limit, $windowSeconds): void {
            if (!is_dir($this->directory)) {
                $cursor = $this->directory;

                while ($cursor !== dirname($cursor)) {
                    if (is_file($cursor)) {
                        throw new RuntimeException('Unable to create the rate limit directory.');
                    }

                    $cursor = dirname($cursor);
                }

                if (!mkdir($this->directory, 0755, true) && !is_dir($this->directory)) {
                    throw new RuntimeException('Unable to create the rate limit directory.');
                }
            }

            $path = $this->directory.'/'.hash('sha256', $bucket.'|'.$identity);

            if (is_dir($path)) {
                throw new RuntimeException('Unable to open the rate limit file.');
            }

            $handle = fopen($path, 'c+');

            if ($handle === false) {
                throw new RuntimeException('Unable to open the rate limit file.');
            }

            try {
                if (!flock($handle, LOCK_EX)) {
                    throw new RuntimeException('Unable to lock the rate limit file.');
                }

                $raw = stream_get_contents($handle);

                if ($raw === false) {
                    throw new RuntimeException('Unable to read the rate limit file.');
                }

                $now = $this->clock->now()->getTimestamp();
                $window = RateLimitWindow::parse($raw, $now - $windowSeconds)->record($now, $limit);

                rewind($handle);

                if (!ftruncate($handle, 0) || fwrite($handle, $window->toString()) === false || !fflush($handle)) {
                    throw new RuntimeException('Unable to write the rate limit file.');
                }
            } finally {
                flock($handle, LOCK_UN);
                fclose($handle);
            }
        });
    }
}
