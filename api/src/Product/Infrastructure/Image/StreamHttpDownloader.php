<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Image;

use App\Product\Application\Image\AllowedRemoteImageUrl;
use App\Product\Application\Image\HttpDownloader;
use RuntimeException;

final class StreamHttpDownloader implements HttpDownloader
{
    public function __construct(private AllowedRemoteImageUrl $allowedRemoteImageUrl)
    {
    }

    public function get(string $url): string
    {
        $this->allowedRemoteImageUrl->assertSafe($url);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: ShoppyFixtureLoader/1.0\r\nAccept: image/*\r\n",
                'timeout' => 30,
                'follow_location' => 0,
                'max_redirects' => 0,
                'ignore_errors' => false,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $contents = @file_get_contents($url, false, $context);

        if ($contents === false || $contents === '') {
            throw new RuntimeException('Unable to download product image: '.$url);
        }

        return $contents;
    }
}
