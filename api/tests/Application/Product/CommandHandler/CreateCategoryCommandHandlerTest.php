<?php

declare(strict_types=1);

use App\Product\Application\Command\CreateCategoryCommand;
use App\Product\Application\CommandHandler\CreateCategoryCommandHandler;
use App\Product\Application\QueryHandler\ListCategoriesQueryHandler;
use App\Product\Application\UniqueCategorySlug;
use App\Product\Domain\Exception\InvalidCategoryName;
use App\Product\Infrastructure\Persistence\InMemoryCategoryRepository;

it('creates a category and lists categories by name', function () {
    $repository = new InMemoryCategoryRepository();
    $handler = new CreateCategoryCommandHandler($repository, new UniqueCategorySlug($repository));

    $first = $handler->handle(new CreateCategoryCommand('Textile'));
    $second = $handler->handle(new CreateCategoryCommand('Accessoires'));

    $items = (new ListCategoriesQueryHandler($repository))->handle();

    expect($first->slug()->value())->toBe('textile')
        ->and($second->slug()->value())->toBe('accessoires')
        ->and(array_map(static fn ($category) => $category->name, $items))->toBe(['Accessoires', 'Textile']);
});

it('numbers a category slug when the name is already used', function () {
    $repository = new InMemoryCategoryRepository();
    $handler = new CreateCategoryCommandHandler($repository, new UniqueCategorySlug($repository));

    $handler->handle(new CreateCategoryCommand('Textile'));
    $copy = $handler->handle(new CreateCategoryCommand('Textile'));

    expect($copy->slug()->value())->toBe('textile-2');
});

it('rejects an empty category name', function () {
    $repository = new InMemoryCategoryRepository();
    $handler = new CreateCategoryCommandHandler($repository, new UniqueCategorySlug($repository));

    $handler->handle(new CreateCategoryCommand('   '));
})->throws(InvalidCategoryName::class);
