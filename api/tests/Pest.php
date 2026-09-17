<?php

declare(strict_types=1);

use App\Product\Application\CommandHandler\CreateProductCommandHandler;
use App\Product\Application\CommandHandler\UpdateProductCommandHandler;
use App\Product\Application\UniqueProductSlug;
use App\Product\Domain\Repository\ProductRepository;
use App\Shared\Domain\Clock;
use App\Tests\Doctrine\DatabaseSchema;
use App\Tests\Support\FunctionalTestCase;
use App\Tests\Support\IntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;

require_once __DIR__.'/Support/AuthHeaders.php';

function createProducts(ProductRepository $repository, Clock $clock): CreateProductCommandHandler
{
    return new CreateProductCommandHandler($repository, $clock, new UniqueProductSlug($repository));
}

function updateProducts(ProductRepository $repository): UpdateProductCommandHandler
{
    return new UpdateProductCommandHandler($repository, new UniqueProductSlug($repository));
}

function clearTestUploads(string $directory): void
{
    if ($directory === '' || !is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($iterator as $file) {
        $pathname = $file->getPathname();

        if ($file->isDir()) {
            rmdir($pathname);
            continue;
        }

        unlink($pathname);
    }
}

uses(IntegrationTestCase::class)
    ->beforeAll(function (): void {
        IntegrationTestCase::bootApplicationKernel();
        DatabaseSchema::initialize(IntegrationTestCase::getContainer()->get(EntityManagerInterface::class));
    })
    ->beforeEach(function (): void {
        $entityManager = IntegrationTestCase::getContainer()->get(EntityManagerInterface::class);
        DatabaseSchema::reset($entityManager);
        DatabaseSchema::beginTransaction($entityManager);
    })
    ->afterEach(function (): void {
        DatabaseSchema::rollback(IntegrationTestCase::getContainer()->get(EntityManagerInterface::class));
    })
    ->afterAll(function (): void {
        IntegrationTestCase::shutdownKernel();
    })
    ->in('Integration');

uses(FunctionalTestCase::class)
    ->beforeAll(function (): void {
        FunctionalTestCase::browser();
        DatabaseSchema::initialize(FunctionalTestCase::getContainer()->get(EntityManagerInterface::class));
    })
    ->beforeEach(function (): void {
        $this->client = FunctionalTestCase::browser();
        DatabaseSchema::reset(FunctionalTestCase::getContainer()->get(EntityManagerInterface::class));
        clearCatalogHeaderCache();
        clearTestUploads((string) FunctionalTestCase::getContainer()->getParameter('app.media.upload_dir'));
    })
    ->afterAll(function (): void {
        FunctionalTestCase::shutdownKernel();
    })
    ->in('Functional');
