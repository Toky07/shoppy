<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Http;

use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Infrastructure\Security\AuthenticatedUser;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class CurrentUser
{
    public const USER_ID = 'auth.user_id';

    public const ROLE = 'auth.role';

    public const TOKEN = 'auth.token';

    public function __construct(
        private RequestStack $requests,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function id(): string
    {
        return $this->idOrNull() ?? throw new Unauthenticated();
    }

    public function idOrNull(): ?string
    {
        $user = $this->securityUser();

        if ($user !== null) {
            return $user->id();
        }

        return $this->attribute(self::USER_ID);
    }

    public function isAuthenticated(): bool
    {
        return $this->idOrNull() !== null;
    }

    public function token(): string
    {
        $user = $this->securityUser();

        if ($user !== null) {
            return $user->accessToken();
        }

        $this->id();

        return $this->attribute(self::TOKEN) ?? throw new Unauthenticated();
    }

    private function securityUser(): ?AuthenticatedUser
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        return $user instanceof AuthenticatedUser ? $user : null;
    }

    private function attribute(string $name): ?string
    {
        $request = $this->requests->getCurrentRequest();

        if ($request === null) {
            return null;
        }

        $value = $request->attributes->get($name);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
