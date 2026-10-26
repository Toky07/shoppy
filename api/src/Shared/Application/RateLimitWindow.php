<?php

declare(strict_types=1);

namespace App\Shared\Application;

use App\Shared\Domain\Exception\RateLimited;

final readonly class RateLimitWindow
{
    /** @param list<int> $hits */
    private function __construct(private array $hits)
    {
    }

    public static function parse(string $raw, int $threshold): self
    {
        $hits = [];

        if ($raw !== '') {
            foreach (explode(',', $raw) as $stamp) {
                if ($stamp !== '' && (int) $stamp > $threshold) {
                    $hits[] = (int) $stamp;
                }
            }
        }

        return new self($hits);
    }

    public function record(int $now, int $limit): self
    {
        if (count($this->hits) >= $limit) {
            throw new RateLimited();
        }

        return new self([...$this->hits, $now]);
    }

    public function toString(): string
    {
        return implode(',', array_map(strval(...), $this->hits));
    }
}
