<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Http;

use App\Auth\Domain\Exception\Unauthenticated;
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

    public function isAuthenticated(): bool
    {
        return $this->idOrNull() !== null;
    }

    public function token(): string
    {
        $this->id();

        return $this->attribute(self::TOKEN) ?? throw new Unauthenticated();
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
