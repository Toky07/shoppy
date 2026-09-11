<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Image;

use App\Product\Application\Image\ProductImageWriter;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class SvgProductImageWriter implements ProductImageWriter
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private string $publicDir,
    ) {
    }

    public function write(string $slug, string $label): string
    {
        $filename = $this->filename($slug);
        $directory = $this->publicDir.'/media/products';

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create product image directory.');
        }

        $path = $directory.'/'.$filename;

        if (!is_file($path)) {
            file_put_contents($path, $this->svg($label, $filename));
        }

        return '/media/products/'.$filename;
    }

    private function filename(string $slug): string
    {
        $normalized = strtolower(preg_replace('/[^a-zA-Z0-9._-]+/', '-', $slug) ?? $slug);
        $normalized = trim($normalized, '-');

        if ($normalized === '') {
            $normalized = 'product';
        }

        if (!str_ends_with($normalized, '.svg')) {
            $normalized .= '.svg';
        }

        return $normalized;
    }

    private function svg(string $label, string $filename): string
    {
        $hue = crc32($filename) % 360;
        $safeLabel = htmlspecialchars($label, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800" role="img" aria-label="{$safeLabel}">
  <rect width="800" height="800" rx="48" fill="hsl({$hue} 45% 88%)"/>
  <circle cx="400" cy="330" r="140" fill="hsl({$hue} 55% 42%)"/>
  <rect x="220" y="500" width="360" height="72" rx="36" fill="hsl({$hue} 40% 32%)"/>
  <text x="400" y="720" text-anchor="middle" font-size="36" font-family="sans-serif" fill="hsl({$hue} 30% 22%)">{$safeLabel}</text>
</svg>
SVG;
    }
}
