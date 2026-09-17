<?php

declare(strict_types=1);

namespace App\Product\Application\Image;

interface HttpDownloader
{
    public function get(string $url): string;
}
