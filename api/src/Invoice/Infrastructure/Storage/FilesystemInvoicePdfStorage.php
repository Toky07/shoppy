<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Storage;

use App\Invoice\Application\Port\InvoicePdfStorage;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class FilesystemInvoicePdfStorage implements InvoicePdfStorage
{
    public function __construct(
        #[Autowire('%app.media.upload_dir%')]
        private string $uploadDirectory,
    ) {
    }

    public function store(string $relativePath, string $pdfContent): void
    {
        $absolutePath = $this->uploadDirectory.'/'.$relativePath;
        $directory = dirname($absolutePath);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create invoice PDF directory.');
        }

        if (file_put_contents($absolutePath, $pdfContent) === false) {
            throw new RuntimeException('Unable to write invoice PDF.');
        }

        chmod($absolutePath, 0644);
    }

    public function read(string $relativePath): ?string
    {
        $absolutePath = $this->uploadDirectory.'/'.$relativePath;

        if (!is_file($absolutePath)) {
            return null;
        }

        $contents = file_get_contents($absolutePath);

        return $contents === false ? null : $contents;
    }
}
