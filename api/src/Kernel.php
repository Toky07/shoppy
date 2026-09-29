<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        $cacheDir = parent::getCacheDir();

        if ($this->environment !== 'test') {
            return $cacheDir;
        }

        $token = getenv('TEST_TOKEN');

        if (is_string($token) && $token !== '') {
            return $cacheDir.'_'.$token;
        }

        return $cacheDir;
    }
}
