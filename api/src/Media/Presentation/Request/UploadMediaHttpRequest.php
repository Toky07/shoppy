<?php

declare(strict_types=1);

namespace App\Media\Presentation\Request;

use App\Media\Application\DetectedMediaType;
use App\Shared\Presentation\Exception\InvalidRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final readonly class UploadMediaHttpRequest
{
    public function __construct(
        public string $ownerType,
        public string $ownerId,
        public string $originalFilename,
        public string $mimeType,
        public string $binaryContent,
        public ?int $position,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            throw InvalidRequest::of('This field is required.', 'file');
        }

        $ownerType = $request->request->get('ownerType');
        if (!is_string($ownerType) || trim($ownerType) === '') {
            throw InvalidRequest::of('This field is required.', 'ownerType');
        }

        $ownerId = $request->request->get('ownerId');
        if (!is_string($ownerId) || trim($ownerId) === '') {
            throw InvalidRequest::of('This field is required.', 'ownerId');
        }

        $position = $request->request->get('position');
        if ($position === null || $position === '') {
            $parsedPosition = null;
        } elseif (is_numeric($position) && (string) (int) $position === (string) $position) {
            $parsedPosition = (int) $position;
        } else {
            throw InvalidRequest::of('This value must be an integer.', 'position');
        }

        $contents = $file->getContent();

        return new self(
            $ownerType,
            $ownerId,
            $file->getClientOriginalName(),
            DetectedMediaType::fromBinary($contents)->value(),
            $contents,
            $parsedPosition,
        );
    }
}
