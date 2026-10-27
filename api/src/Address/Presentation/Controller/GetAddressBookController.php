<?php

declare(strict_types=1);

namespace App\Address\Presentation\Controller;

use App\Address\Application\Query\GetAddressBookQuery;
use App\Address\Application\QueryHandler\GetAddressBookQueryHandler;
use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class GetAddressBookController
{
    public function __construct(
        private CurrentUser $currentUser,
        private GetAddressBookQueryHandler $getAddressBook,
    ) {
    }

    #[Route('/addresses', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(): JsonResponse
    {
        $book = $this->getAddressBook->handle(new GetAddressBookQuery($this->currentUser->id()));

        return new JsonResponse($book->toArray());
    }
}
