<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use Doctrine\ORM\Decorator\EntityManagerDecorator;

final class CountingEntityManager extends EntityManagerDecorator
{
    public int $flushCalls = 0;

    public function flush(): void
    {
        $this->flushCalls++;
        parent::flush();
    }
}
