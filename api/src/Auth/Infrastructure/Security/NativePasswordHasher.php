<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Application\PasswordHasher;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\PlainPassword;
use RuntimeException;

final class NativePasswordHasher implements PasswordHasher
{
    public function hash(PlainPassword $password): HashedPassword
    {
        $hash = password_hash($password->value(), PASSWORD_ARGON2ID);

        if ($hash === false) {
            throw new RuntimeException('Unable to hash the password.');
        }

        return HashedPassword::fromHash($hash);
    }

    public function verify(HashedPassword $hash, PlainPassword $password): bool
    {
        return password_verify($password->value(), $hash->value());
    }
}
