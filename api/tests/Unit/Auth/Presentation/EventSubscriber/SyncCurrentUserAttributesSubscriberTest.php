<?php

declare(strict_types=1);

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Auth\Presentation\EventSubscriber\SyncCurrentUserAttributesSubscriber;
use App\Auth\Presentation\Http\CurrentUser;
use App\User\Domain\ValueObject\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

function mainRequestEvent(Request $request): RequestEvent
{
    $kernel = new class implements HttpKernelInterface {
        public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
        {
            return new Response();
        }
    };

    return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
}

it('syncs security token user onto request attributes', function () {
    $userId = '11111111-1111-4111-8111-111111111111';
    $authenticatedUser = AuthenticatedUser::fromDomainRole($userId, Role::customer(), 'session-token');
    $tokenStorage = new TokenStorage();
    $tokenStorage->setToken(new PostAuthenticationToken($authenticatedUser, 'main', $authenticatedUser->getRoles()));

    $request = Request::create('/cart');
    $subscriber = new SyncCurrentUserAttributesSubscriber($tokenStorage);
    $subscriber->onRequest(mainRequestEvent($request));

    expect($request->attributes->get(CurrentUser::USER_ID))->toBe($userId)
        ->and($request->attributes->get(CurrentUser::ROLE))->toBe(Role::customer()->value())
        ->and($request->attributes->get(CurrentUser::TOKEN))->toBe('session-token');
});

it('does not overwrite attributes already set by the authenticator', function () {
    $authenticatedUser = AuthenticatedUser::fromDomainRole(
        '22222222-2222-4222-8222-222222222222',
        Role::admin(),
        'other-token',
    );
    $tokenStorage = new TokenStorage();
    $tokenStorage->setToken(new PostAuthenticationToken($authenticatedUser, 'main', $authenticatedUser->getRoles()));

    $request = Request::create('/cart');
    $request->attributes->set(CurrentUser::USER_ID, 'existing-id');

    (new SyncCurrentUserAttributesSubscriber($tokenStorage))->onRequest(mainRequestEvent($request));

    expect($request->attributes->get(CurrentUser::USER_ID))->toBe('existing-id')
        ->and($request->attributes->has(CurrentUser::ROLE))->toBeFalse();
});

it('ignores sub-requests', function () {
    $authenticatedUser = AuthenticatedUser::fromDomainRole(
        '11111111-1111-4111-8111-111111111111',
        Role::customer(),
        'session-token',
    );
    $tokenStorage = new TokenStorage();
    $tokenStorage->setToken(new PostAuthenticationToken($authenticatedUser, 'main', $authenticatedUser->getRoles()));

    $request = Request::create('/cart');
    $kernel = new class implements HttpKernelInterface {
        public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
        {
            return new Response();
        }
    };
    $event = new RequestEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

    (new SyncCurrentUserAttributesSubscriber($tokenStorage))->onRequest($event);

    expect($request->attributes->has(CurrentUser::USER_ID))->toBeFalse();
});
