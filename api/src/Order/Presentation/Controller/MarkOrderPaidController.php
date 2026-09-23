<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Order\Application\Command\MarkOrderPaidCommand;
use App\Order\Application\CommandHandler\MarkOrderPaidCommandHandler;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class MarkOrderPaidController
{
    public function __construct(
        private CurrentUser $currentUser,
        private MarkOrderPaidCommandHandler $markOrderPaid,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders/{id}/mark-paid', methods: ['POST'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $this->currentUser->requireAdmin();

        $this->markOrderPaid->handle(new MarkOrderPaidCommand($id));

        $order = $this->getOrder->handle(new GetOrderQuery($id));

        return new JsonResponse($order->toArray());
    }
}
