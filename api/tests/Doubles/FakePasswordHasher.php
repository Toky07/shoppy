<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Auth\Application\PasswordHasher;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\PlainPassword;

final class FakePasswordHasher implements PasswordHasher
{
    public function hash(PlainPassword $password): HashedPassword
    {
        return HashedPassword::fromHash('hashed:'.$password->value());
    }

    public function verify(HashedPassword $hash, PlainPassword $password): bool
    {
        return $hash->value() === 'hashed:'.$password->value();
    }
}
