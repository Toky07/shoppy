<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security\Http;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Auth\Presentation\Http\AccessCredential;
use App\Auth\Presentation\Http\CurrentUser;
use App\User\Domain\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class AccessTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private UserRepository $users,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return AccessCredential::fromRequest($request) !== null;
    }

    public function authenticate(Request $request): Passport
    {
        $accessToken = AccessCredential::fromRequest($request);

        if ($accessToken === null) {
            throw new InvalidAccessTokenAuthenticationException();
        }

        try {
            $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery($accessToken));
        } catch (Unauthenticated) {
            throw new InvalidAccessTokenAuthenticationException();
        }

        $user = $this->users->findById($userId);

        if ($user === null || $user->isDeleted()) {
            throw new InvalidAccessTokenAuthenticationException();
        }

        $authenticatedUser = AuthenticatedUser::fromDomainRole(
            $userId->value(),
            $user->role(),
            $accessToken,
        );

        return new SelfValidatingPassport(
            new UserBadge($userId->value(), static fn (): AuthenticatedUser => $authenticatedUser),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        if ($user instanceof AuthenticatedUser) {
            $request->attributes->set(CurrentUser::USER_ID, $user->id());
            $request->attributes->set(CurrentUser::ROLE, $user->domainRole()->value());
            $request->attributes->set(CurrentUser::TOKEN, $user->accessToken());
        }

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
