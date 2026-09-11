<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

interface InvalidValue extends DomainException
{
    public function field(): string;
}
