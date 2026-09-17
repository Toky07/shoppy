<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Product\Application\Image\HttpDownloader;
use RuntimeException;

final class FakeHttpDownloader implements HttpDownloader
{
    /** @var list<string> */
    public array $urls = [];

    /**
     * @param array<string, string> $responses
     */
    public function __construct(private array $responses = [])
    {
    }

    public function get(string $url): string
    {
        $this->urls[] = $url;

        if (!isset($this->responses[$url])) {
            throw new RuntimeException('Unexpected download: '.$url);
        }

        return $this->responses[$url];
    }
}
