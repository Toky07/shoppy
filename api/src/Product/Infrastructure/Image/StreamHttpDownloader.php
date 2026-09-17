<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Image;

use App\Product\Application\Image\HttpDownloader;
use RuntimeException;

final class StreamHttpDownloader implements HttpDownloader
{
    public function get(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: ShoppyFixtureLoader/1.0\r\nAccept: image/*\r\n",
                'timeout' => 30,
                'follow_location' => 1,
                'ignore_errors' => false,
            ],
        ]);

        $contents = @file_get_contents($url, false, $context);

        if ($contents === false || $contents === '') {
            throw new RuntimeException('Unable to download product image: '.$url);
        }

        return $contents;
    }
}
