<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Order\Application\Command\MarkOrderPaidCommand;
use App\Order\Application\CommandHandler\MarkOrderPaidCommandHandler;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class MarkOrderPaidController
{
    public function __construct(
        private MarkOrderPaidCommandHandler $markOrderPaid,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders/{id}/mark-paid', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]
    public function __invoke(string $id): JsonResponse
    {
        $this->markOrderPaid->handle(new MarkOrderPaidCommand($id));

        $order = $this->getOrder->handle(new GetOrderQuery($id));

        return new JsonResponse($order->toArray());
    }
}
