<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Http;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Domain\Exception\Unauthenticated;
use App\User\Domain\ValueObject\Role;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class CurrentUser
{
    public const USER_ID = 'auth.user_id';

    public const ROLE = 'auth.role';

    public const TOKEN = 'auth.token';

    public function __construct(private RequestStack $requests)
    {
    }

    public function id(): string
    {
        return $this->attribute(self::USER_ID) ?? throw new Unauthenticated();
    }

    public function idOrNull(): ?string
    {
        return $this->attribute(self::USER_ID);
    }

    public function token(): string
    {
        $this->id();

        return $this->attribute(self::TOKEN) ?? throw new Unauthenticated();
    }

    public function requireAdmin(): string
    {
        $id = $this->id();

        if ($this->attribute(self::ROLE) !== Role::admin()->value()) {
            throw new Forbidden();
        }

        return $id;
    }

    public function assertSelfOrAdmin(string $userId): void
    {
        if ($this->id() === $userId) {
            return;
        }

        if ($this->attribute(self::ROLE) !== Role::admin()->value()) {
            throw new Forbidden();
        }
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
