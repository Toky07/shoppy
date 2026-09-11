<?php

declare(strict_types=1);

use App\Tests\Doctrine\DatabaseSchema;
use App\Tests\Support\FunctionalTestCase;
use App\Tests\Support\IntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;

require_once __DIR__.'/Support/AuthHeaders.php';

uses(IntegrationTestCase::class)
    ->beforeAll(function (): void {
        IntegrationTestCase::bootApplicationKernel();
        DatabaseSchema::initialize(IntegrationTestCase::getContainer()->get(EntityManagerInterface::class));
    })
    ->beforeEach(function (): void {
        DatabaseSchema::beginTransaction(IntegrationTestCase::getContainer()->get(EntityManagerInterface::class));
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
    })
    ->afterAll(function (): void {
        FunctionalTestCase::shutdownKernel();
    })
    ->in('Functional');
