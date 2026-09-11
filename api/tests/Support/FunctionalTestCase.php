<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTestCase extends WebTestCase
{
    public static function browser(): KernelBrowser
    {
        if (!static::$booted) {
            static::createClient();
        }

        return static::getClient();
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
