<?php

declare(strict_types=1);

namespace App\Address\Presentation\Controller;

use App\Address\Application\Command\MarkDefaultAddressCommand;
use App\Address\Application\CommandHandler\MarkDefaultAddressCommandHandler;
use App\Address\Application\Query\GetAddressBookQuery;
use App\Address\Application\QueryHandler\GetAddressBookQueryHandler;
use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class MarkDefaultAddressController
{
    public function __construct(
        private CurrentUser $currentUser,
        private MarkDefaultAddressCommandHandler $markDefault,
        private GetAddressBookQueryHandler $getAddressBook,
    ) {
    }

    #[Route('/addresses/{addressId}/default', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(string $addressId): JsonResponse
    {
        $userId = $this->currentUser->id();
        $this->markDefault->handle(new MarkDefaultAddressCommand($userId, $addressId));
        $book = $this->getAddressBook->handle(new GetAddressBookQuery($userId));

        return new JsonResponse($book->toArray());
    }
}
