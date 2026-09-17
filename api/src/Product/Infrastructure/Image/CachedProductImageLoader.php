<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Image;

use App\Media\Domain\ValueObject\MediaMimeType;
use App\Product\Application\Image\GeneratedProductImage;
use App\Product\Application\Image\HttpDownloader;
use App\Product\Application\Image\ProductImageLoader;
use finfo;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class CachedProductImageLoader implements ProductImageLoader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/var/fixture-images')]
        private string $cacheDirectory,
        private HttpDownloader $httpDownloader,
    ) {
    }

    public function load(string $source): GeneratedProductImage
    {
        $contents = $this->isRemote($source) ? $this->cachedRemote($source) : $this->readLocal($source);
        $mimeType = MediaMimeType::fromString($this->detectMimeType($contents));

        return new GeneratedProductImage(
            $this->filenameFor($source, $mimeType->extension()),
            $mimeType->value(),
            $contents,
        );
    }

    private function cachedRemote(string $url): string
    {
        if (!is_dir($this->cacheDirectory) && !mkdir($this->cacheDirectory, 0777, true) && !is_dir($this->cacheDirectory)) {
            throw new RuntimeException('Unable to create product image cache directory.');
        }

        $cacheFile = $this->cacheDirectory.'/'.sha1($url);

        if (!is_file($cacheFile)) {
            file_put_contents($cacheFile, $this->httpDownloader->get($url));
        }

        return $this->readLocal($cacheFile);
    }

    private function readLocal(string $path): string
    {
        if (!is_file($path)) {
            throw new RuntimeException('Unable to read product image: '.$path);
        }

        $contents = file_get_contents($path);

        if ($contents === false || $contents === '') {
            throw new RuntimeException('Product image is empty: '.$path);
        }

        return $contents;
    }

    private function detectMimeType(string $contents): string
    {
        $detected = (new finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: '';
        $detected = strtolower(trim(explode(';', $detected)[0]));

        return $detected;
    }

    private function filenameFor(string $source, string $extension): string
    {
        $path = $this->isRemote($source) ? (parse_url($source, PHP_URL_PATH) ?: $source) : $source;
        $filename = strtolower(basename($path));
        $filename = preg_replace('/[^a-z0-9._-]+/', '-', $filename) ?? $filename;
        $filename = trim($filename, '-');

        if ($filename === '') {
            $filename = 'image';
        }

        $name = pathinfo($filename, PATHINFO_FILENAME) ?: 'image';

        return $name.'.'.$extension;
    }

    private function isRemote(string $source): bool
    {
        return str_starts_with($source, 'http://') || str_starts_with($source, 'https://');
    }
}
