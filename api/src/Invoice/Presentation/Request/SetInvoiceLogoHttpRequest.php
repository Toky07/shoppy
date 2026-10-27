<?php

declare(strict_types=1);

namespace App\Invoice\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final readonly class SetInvoiceLogoHttpRequest
{
    public function __construct(public string $binaryContent)
    {
    }

    public static function fromRequest(Request $request): self
    {
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            throw InvalidRequest::of('This field is required.', 'file');
        }

        return new self($file->getContent());
    }
}
