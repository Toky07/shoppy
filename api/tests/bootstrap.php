<?php

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Paratest sets TEST_TOKEN per worker (see the doctrine dbname_suffix and Kernel::getCacheDir).
if (is_string($testToken = getenv('TEST_TOKEN')) && $testToken !== '') {
    $_SERVER['TEST_TOKEN'] = $testToken;
    $_ENV['TEST_TOKEN'] = $testToken;
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Tests run with APP_DEBUG=0, so the kernel never rebuilds a stale container on its own.
if (getenv('TEST_TOKEN') === false) {
    $testCacheDir = dirname(__DIR__).'/var/cache/test';
    $container = $testCacheDir.'/App_KernelTestContainer.php';

    if (is_file($container) && !(new ConfigCache($container, true))->isFresh()) {
        (new Filesystem())->remove($testCacheDir);
    }
}
