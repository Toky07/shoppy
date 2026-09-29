<?php

declare(strict_types=1);

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Auth\Presentation\Security\GrantChecker;
use App\User\Domain\ValueObject\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

uses(PHPUnit\Framework\TestCase::class);

function grantChecker(
    AuthorizationCheckerInterface $authorizationChecker,
    ?AuthenticatedUser $user = null,
): GrantChecker {
    $tokenStorage = new TokenStorage();

    if ($user !== null) {
        $tokenStorage->setToken(new PostAuthenticationToken($user, 'main', $user->getRoles()));
    }

    $requestStack = new RequestStack();
    $requestStack->push(Request::create('/'));

    return new GrantChecker($authorizationChecker, new CurrentUser($requestStack, $tokenStorage));
}

it('does nothing when access is granted', function () {
    $checker = $this->createMock(AuthorizationCheckerInterface::class);
    $checker->method('isGranted')->willReturn(true);

    grantChecker($checker)->denyUnlessGranted(AuthorizationAttributes::ORDER_VIEW);

    expect(true)->toBeTrue();
});

it('throws forbidden when access is denied on an authenticated route', function () {
    $checker = $this->createMock(AuthorizationCheckerInterface::class);
    $checker->method('isGranted')->willReturn(false);

    grantChecker($checker)->denyUnlessGranted(AuthorizationAttributes::ORDER_VIEW);
})->throws(Forbidden::class);

it('throws unauthenticated when access is denied and the caller is anonymous', function () {
    $checker = $this->createMock(AuthorizationCheckerInterface::class);
    $checker->method('isGranted')->willReturn(false);

    grantChecker($checker)->denyUnlessGrantedOrRequireAuthentication(AuthorizationAttributes::PRODUCT_ADMIN);
})->throws(Unauthenticated::class);

it('throws forbidden when access is denied for a logged-in non-admin', function () {
    $checker = $this->createMock(AuthorizationCheckerInterface::class);
    $checker->method('isGranted')->willReturn(false);

    $user = AuthenticatedUser::fromDomainRole(
        '11111111-1111-4111-8111-111111111111',
        Role::customer(),
        'token',
    );

    grantChecker($checker, $user)->denyUnlessGrantedOrRequireAuthentication(AuthorizationAttributes::PRODUCT_ADMIN);
})->throws(Forbidden::class);
