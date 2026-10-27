<?php

declare(strict_types=1);

use App\Auth\Application\IssueSession;
use App\Auth\Domain\Entity\Credentials;
use App\Auth\Domain\ValueObject\PlainPassword;
use App\Auth\Infrastructure\Persistence\InMemoryAccessTokenRepository;
use App\Auth\Infrastructure\Persistence\InMemoryCredentialsRepository;
use App\Cart\Application\Command\GuestCheckoutCommand;
use App\Cart\Application\Command\GuestCheckoutLine;
use App\Cart\Application\CommandHandler\GuestCheckoutCommandHandler;
use App\Cart\Domain\Exception\GuestCheckoutRequiresAccount;
use App\Order\Application\CommandHandler\PlaceOrderCommandHandler;
use App\Order\Application\Response\CatalogSnapshot;
use App\Order\Domain\ValueObject\OrderStatus;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Tests\Doubles\FakeCatalog;
use App\Tests\Doubles\FakePasswordHasher;
use App\Tests\Doubles\FakeTokenGenerator;
use App\Tests\Doubles\FixedClock;
use App\Tests\Doubles\ImmediateTransactionRunner;
use App\Tests\Doubles\RecordingEventDispatcher;
use App\User\Application\CommandHandler\RegisterUserCommandHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

function guestCheckoutHandler(
    InMemoryUserRepository $users,
    InMemoryOrderRepository $orders,
    InMemoryCredentialsRepository $credentials = new InMemoryCredentialsRepository(),
    FakeCatalog $catalog = new FakeCatalog(),
    DateTimeImmutable $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
): GuestCheckoutCommandHandler {
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: 10,
    ));

    return new GuestCheckoutCommandHandler(
        $users,
        $credentials,
        new RegisterUserCommandHandler($users, new FixedClock($now)),
        new PlaceOrderCommandHandler(
            $orders,
            $catalog,
            new FixedClock($now),
            new ImmediateTransactionRunner(new RecordingEventDispatcher()),
        ),
        new IssueSession(new InMemoryAccessTokenRepository(), new FakeTokenGenerator(), new FixedClock($now)),
    );
}

it('creates a guest customer, places the order and issues a session', function () {
    $users = new InMemoryUserRepository();
    $orders = new InMemoryOrderRepository();
    $handler = guestCheckoutHandler($users, $orders);

    $result = $handler->handle(new GuestCheckoutCommand(
        email: 'Ada@Nuvora.test',
        items: [new GuestCheckoutLine('550e8400-e29b-41d4-a716-446655440000', 2)],
        shippingAddress: sampleOrderAddress(),
        billingAddress: sampleOrderAddress(),
        shippingMethod: 'standard',
    ));

    $user = $users->findByEmail(Email::fromString('ada@nuvora.test'));
    $order = $orders->findById($result->orderId);

    expect($user)->not->toBeNull()
        ->and($user->isEmailVerified())->toBeFalse()
        ->and($result->session->userId->value())->toBe($user->id()->value())
        ->and($result->session->accessToken)->not->toBe('')
        ->and($order)->not->toBeNull()
        ->and($order->customerId()->value())->toBe($user->id()->value())
        ->and($order->status())->toEqual(OrderStatus::pending())
        ->and($order->items()[0]->quantity()->value())->toBe(2);
});

it('reuses a guest customer without credentials', function () {
    $users = new InMemoryUserRepository();
    $orders = new InMemoryOrderRepository();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $existing = User::register(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        Email::fromString('guest@nuvora.test'),
        $now,
    );
    $users->save($existing);
    $handler = guestCheckoutHandler($users, $orders);

    $result = $handler->handle(new GuestCheckoutCommand(
        email: 'guest@nuvora.test',
        items: [new GuestCheckoutLine('550e8400-e29b-41d4-a716-446655440000', 1)],
        shippingAddress: sampleOrderAddress(),
        billingAddress: sampleOrderAddress(),
        shippingMethod: 'standard',
    ));

    expect($result->session->userId->value())->toBe('11111111-1111-4111-8111-111111111111')
        ->and($users->findByEmail(Email::fromString('guest@nuvora.test'))?->id()->value())
        ->toBe('11111111-1111-4111-8111-111111111111');
});

it('refuses guest checkout when the email already has a password', function () {
    $users = new InMemoryUserRepository();
    $credentials = new InMemoryCredentialsRepository();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $user = User::register(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        Email::fromString('ada@nuvora.test'),
        $now,
    );
    $users->save($user);
    $credentials->save(Credentials::create(
        $user->id(),
        (new FakePasswordHasher())->hash(PlainPassword::fromString('secret-secret')),
    ));

    guestCheckoutHandler($users, new InMemoryOrderRepository(), $credentials)->handle(new GuestCheckoutCommand(
        email: 'ada@nuvora.test',
        items: [new GuestCheckoutLine('550e8400-e29b-41d4-a716-446655440000', 1)],
        shippingAddress: sampleOrderAddress(),
        billingAddress: sampleOrderAddress(),
        shippingMethod: 'standard',
    ));
})->throws(GuestCheckoutRequiresAccount::class);
