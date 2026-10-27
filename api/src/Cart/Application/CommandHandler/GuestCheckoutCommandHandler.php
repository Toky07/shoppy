<?php

declare(strict_types=1);

namespace App\Cart\Application\CommandHandler;

use App\Auth\Application\IssueSession;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Cart\Application\Command\GuestCheckoutCommand;
use App\Cart\Application\Command\GuestCheckoutLine;
use App\Cart\Application\Response\GuestCheckoutResult;
use App\Cart\Domain\Exception\GuestCheckoutRequiresAccount;
use App\Order\Application\Command\PlaceOrderCommand;
use App\Order\Application\Command\PlaceOrderLine;
use App\Order\Application\CommandHandler\PlaceOrderCommandHandler;
use App\User\Application\Command\RegisterUserCommand;
use App\User\Application\CommandHandler\RegisterUserCommandHandler;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;

final readonly class GuestCheckoutCommandHandler
{
    public function __construct(
        private UserRepository $users,
        private CredentialsRepository $credentials,
        private RegisterUserCommandHandler $registerUser,
        private PlaceOrderCommandHandler $placeOrder,
        private IssueSession $issueSession,
    ) {
    }

    public function handle(GuestCheckoutCommand $command): GuestCheckoutResult
    {
        $userId = $this->customerId($command->email);
        $orderId = $this->placeOrder->handle(new PlaceOrderCommand(
            customerId: $userId->value(),
            items: array_map(
                static fn (GuestCheckoutLine $line): PlaceOrderLine => new PlaceOrderLine(
                    $line->productId,
                    $line->quantity,
                    $line->variantId,
                ),
                $command->items,
            ),
            shippingAddress: $command->shippingAddress,
            billingAddress: $command->billingAddress,
            shippingMethod: $command->shippingMethod,
        ));

        return new GuestCheckoutResult($orderId, $this->issueSession->issue($userId));
    }

    private function customerId(string $emailAddress): UserId
    {
        $email = Email::fromString($emailAddress);
        $user = $this->users->findByEmail($email);

        if ($user === null) {
            return $this->registerUser->handle(new RegisterUserCommand($email->value()));
        }

        if ($this->credentials->findByUserId($user->id()) !== null) {
            throw new GuestCheckoutRequiresAccount();
        }

        return $user->id();
    }
}
