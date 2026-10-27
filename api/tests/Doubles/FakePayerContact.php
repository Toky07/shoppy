<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Payment\Application\Port\PayerContact;
use App\Payment\Domain\ValueObject\CustomerReference;

final class FakePayerContact implements PayerContact
{
    public function __construct(private ?string $email = 'ada@shoppy.test')
    {
    }

    public function emailFor(CustomerReference $customerId): ?string
    {
        return $this->email;
    }
}
