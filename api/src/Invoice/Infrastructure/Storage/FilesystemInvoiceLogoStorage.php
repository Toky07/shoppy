<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Storage;

use App\Invoice\Application\Port\InvoiceLogoStorage;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class FilesystemInvoiceLogoStorage implements InvoiceLogoStorage
{
    public function __construct(
        #[Autowire('%app.media.upload_dir%')]
        private string $uploadDirectory,
    ) {
    }

    public function store(string $extension, string $binaryContent): string
    {
        $directory = $this->uploadDirectory.'/invoices/logos';

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create invoice logo directory.');
        }

        $filename = bin2hex(random_bytes(16)).'.'.$extension;
        $absolutePath = $directory.'/'.$filename;

        if (file_put_contents($absolutePath, $binaryContent) === false) {
            throw new RuntimeException('Unable to write invoice logo.');
        }

        chmod($absolutePath, 0644);

        return 'invoices/logos/'.$filename;
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
