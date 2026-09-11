<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Console;

use App\Product\Application\Command\ImportProductsFromCsvCommand;
use App\Product\Application\CommandHandler\ImportProductsFromCsvCommandHandler;
use App\User\Application\Command\SeedDemoUsersCommand;
use App\User\Application\CommandHandler\SeedDemoUsersCommandHandler;
use App\User\Application\DemoAccounts;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:seed-demo',
    description: 'Seed demo users and import catalog products from CSV.',
)]
final class SeedDemoCommand extends Command
{
    public function __construct(
        private SeedDemoUsersCommandHandler $seedDemoUsers,
        private ImportProductsFromCsvCommandHandler $importProducts,
        #[Autowire('%kernel.project_dir%/fixtures/products.csv')]
        private string $productsCsvPath,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->seedDemoUsers->handle(new SeedDemoUsersCommand());
        $products = $this->importProducts->handle(new ImportProductsFromCsvCommand($this->productsCsvPath));

        $io->success(sprintf('Seeded %d user(s) and imported %d product(s).', $users, $products));
        $io->listing([
            sprintf('Admin: %s / %s', DemoAccounts::ADMIN_EMAIL, DemoAccounts::PASSWORD),
            sprintf('Visiteur: %s / %s', DemoAccounts::VISITOR_EMAIL, DemoAccounts::PASSWORD),
        ]);

        return Command::SUCCESS;
    }
}
