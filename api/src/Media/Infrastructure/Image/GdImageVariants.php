<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Image;

use App\Media\Application\MediaDerivative;
use App\Media\Application\Port\ImageVariants;
use App\Media\Application\Port\MediaStorage;
use GdImage;
use RuntimeException;

final readonly class GdImageVariants implements ImageVariants
{
    public function __construct(private MediaStorage $mediaStorage)
    {
    }

    public function generate(string $relativePath, string $binaryContent, string $mimeType): void
    {
        if ($mimeType === 'image/svg+xml' || $mimeType === 'image/gif') {
            return;
        }

        if (!extension_loaded('gd')) {
            throw new RuntimeException('The GD extension is required to generate image derivatives.');
        }

        $image = @imagecreatefromstring($binaryContent);

        if ($image === false) {
            throw new RuntimeException('Unable to decode uploaded image.');
        }

        foreach (MediaDerivative::cases() as $derivative) {
            $output = $this->resized($image, $derivative->maxWidth());
            $this->mediaStorage->write($derivative->pathFor($relativePath), $this->encode($output, $mimeType));

            if ($output !== $image) {
                imagedestroy($output);
            }
        }

        imagedestroy($image);
    }

    public function delete(string $relativePath): void
    {
        foreach (MediaDerivative::cases() as $derivative) {
            $this->mediaStorage->delete($derivative->pathFor($relativePath));
        }
    }

    private function resized(GdImage $source, int $maxWidth): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);

        if ($width <= $maxWidth) {
            return $source;
        }

        $newWidth = $maxWidth;
        $newHeight = max(1, (int) round($height * ($maxWidth / $width)));
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $resized;
    }

    private function encode(GdImage $image, string $mimeType): string
    {
        ob_start();

        match ($mimeType) {
            'image/jpeg' => imagejpeg($image, null, 82),
            'image/webp' => imagewebp($image, null, 82),
            default => imagepng($image),
        };

        return (string) ob_get_clean();
    }
}
