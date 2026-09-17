<?php

declare(strict_types=1);

namespace App\Media\Application\QueryHandler;

use App\Media\Application\Query\GetMediaQuery;
use App\Media\Application\Response\MediaResponse;
use App\Media\Domain\Exception\MediaNotFound;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaId;

final readonly class GetMediaQueryHandler
{
    public function __construct(private MediaRepository $mediaRepository)
    {
    }

    public function handle(GetMediaQuery $query): MediaResponse
    {
        $id = MediaId::fromString($query->id);
        $media = $this->mediaRepository->findById($id);

        if ($media === null) {
            throw new MediaNotFound($id);
        }

        return MediaResponse::fromMedia($media);
    }
}
