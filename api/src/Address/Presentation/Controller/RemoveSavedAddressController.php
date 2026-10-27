<?php

declare(strict_types=1);

namespace App\Address\Presentation\Controller;

use App\Address\Application\Command\RemoveSavedAddressCommand;
use App\Address\Application\CommandHandler\RemoveSavedAddressCommandHandler;
use App\Address\Application\Query\GetAddressBookQuery;
use App\Address\Application\QueryHandler\GetAddressBookQueryHandler;
use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class RemoveSavedAddressController
{
    public function __construct(
        private CurrentUser $currentUser,
        private RemoveSavedAddressCommandHandler $removeAddress,
        private GetAddressBookQueryHandler $getAddressBook,
    ) {
    }

    #[Route('/addresses/{addressId}', methods: ['DELETE'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(string $addressId): JsonResponse
    {
        $userId = $this->currentUser->id();
        $this->removeAddress->handle(new RemoveSavedAddressCommand($userId, $addressId));
        $book = $this->getAddressBook->handle(new GetAddressBookQuery($userId));

        return new JsonResponse($book->toArray());
    }
}
