<?php

declare(strict_types=1);

namespace App\Media\Application\QueryHandler;

use App\Media\Application\Query\ListMediaByOwnerQuery;
use App\Media\Application\Response\MediaListResponse;
use App\Media\Application\Response\MediaResponse;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;

final readonly class ListMediaByOwnerQueryHandler
{
    public function __construct(private MediaRepository $mediaRepository)
    {
    }

    public function handle(ListMediaByOwnerQuery $query): MediaListResponse
    {
        $items = $this->mediaRepository->findByOwner(
            MediaOwnerType::fromInput($query->ownerType),
            MediaOwnerId::fromString($query->ownerId),
        );

        return new MediaListResponse(array_map(MediaResponse::fromMedia(...), $items));
    }
}
