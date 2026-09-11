<?php

declare(strict_types=1);

namespace App\Auth\Application;

use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\PlainPassword;

interface PasswordHasher
{
    public function hash(PlainPassword $password): HashedPassword;

    public function verify(HashedPassword $hash, PlainPassword $password): bool;
}
