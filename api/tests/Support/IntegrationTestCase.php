<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class IntegrationTestCase extends KernelTestCase
{
    public static function bootApplicationKernel(): void
    {
        if (!static::$booted) {
            static::bootKernel();
        }
    }

    public static function shutdownKernel(): void
    {
        parent::ensureKernelShutdown();
    }

    protected static function ensureKernelShutdown(): void
    {
        // Keep the kernel booted between tests in the same file.
    }
}
