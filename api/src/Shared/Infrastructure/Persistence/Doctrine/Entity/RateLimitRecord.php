<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'rate_limits')]
class RateLimitRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 64)]
    private string $id = '';

    #[ORM\Column(type: 'text')]
    private string $hits = '';
}
