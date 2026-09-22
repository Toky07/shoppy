<?php

declare(strict_types=1);

use App\Order\Application\Command\PlaceOrderAddress;
use App\Order\Domain\ValueObject\PostalAddress;
use App\Order\Domain\ValueObject\ShippingMethod;
use App\Product\Application\CommandHandler\CreateProductCommandHandler;
use App\Product\Application\CommandHandler\UpdateProductCommandHandler;
use App\Product\Application\UniqueProductSku;
use App\Product\Application\UniqueProductSlug;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\CategoryRepository;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\StockQuantity;
use App\Product\Infrastructure\Persistence\InMemoryCategoryRepository;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Shared\Domain\Clock;
use App\Tests\Doctrine\DatabaseSchema;
use App\Tests\Support\FunctionalTestCase;
use App\Tests\Support\IntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;

require_once __DIR__.'/Support/AuthHeaders.php';

function createProducts(ProductRepository $repository, Clock $clock, ?CategoryRepository $categories = null): CreateProductCommandHandler
{
    return new CreateProductCommandHandler(
        $repository,
        $clock,
        new UniqueProductSlug($repository),
        $categories ?? new InMemoryCategoryRepository(),
        new UniqueProductSku($repository),
    );
}

function updateProducts(ProductRepository $repository, ?CategoryRepository $categories = null): UpdateProductCommandHandler
{
    return new UpdateProductCommandHandler(
        $repository,
        new UniqueProductSlug($repository),
        $categories ?? new InMemoryCategoryRepository(),
        new UniqueProductSku($repository),
    );
}

function sampleOrderAddress(): PlaceOrderAddress
{
    return new PlaceOrderAddress(
        'Ada Lovelace',
        '10 rue de la Paix',
        null,
        '75002',
        'Paris',
        'FR',
    );
}

function sampleShippingMethod(): ShippingMethod
{
    return ShippingMethod::quote(ShippingMethod::STANDARD, ShippingMethod::FREE_FROM_CENTS);
}

function samplePostalAddress(): PostalAddress
{
    return PostalAddress::fromInput(
        'Ada Lovelace',
        '10 rue de la Paix',
        null,
        '75002',
        'Paris',
        'FR',
        'address',
    );
}

/**
 * @return array{shippingAddress: array{recipient: string, line1: string, postalCode: string, city: string, country: string}, billingSameAsShipping: true, shippingMethod: string}
 */
function deliveryFields(): array
{
    return [
        'shippingAddress' => [
            'recipient' => 'Ada Lovelace',
            'line1' => '10 rue de la Paix',
            'postalCode' => '75002',
            'city' => 'Paris',
            'country' => 'FR',
        ],
        'billingSameAsShipping' => true,
        'shippingMethod' => 'standard',
    ];
}

function saveProduct(
    InMemoryProductRepository $repository,
    string $id,
    string $name,
    DateTimeImmutable $createdAt,
    int $priceCents = 1999,
    ?string $description = null,
    int $stock = 0,
): void {
    $repository->save(Product::create(
        ProductId::fromString($id),
        ProductName::fromString($name),
        ProductPrice::fromCents($priceCents),
        $createdAt,
        $description === null ? null : ProductDescription::fromString($description),
        StockQuantity::fromInt($stock),
    ));
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
