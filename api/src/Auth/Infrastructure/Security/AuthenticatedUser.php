<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\User\Domain\ValueObject\Role;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class AuthenticatedUser implements UserInterface
{
    public const ROLE_USER = 'ROLE_USER';

    public const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @param list<string> $roles
     */
    public function __construct(
        private string $id,
        private array $roles,
        private string $accessToken,
    ) {
    }

    public static function fromDomainRole(string $userId, Role $role, string $accessToken): self
    {
        $roles = $role->value() === Role::admin()->value()
            ? [self::ROLE_ADMIN, self::ROLE_USER]
            : [self::ROLE_USER];

        return new self($userId, $roles, $accessToken);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function accessToken(): string
    {
        return $this->accessToken;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function isAdmin(): bool
    {
        return in_array(self::ROLE_ADMIN, $this->roles, true);
    }

    public function domainRole(): Role
    {
        return $this->isAdmin() ? Role::admin() : Role::customer();
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }
}
