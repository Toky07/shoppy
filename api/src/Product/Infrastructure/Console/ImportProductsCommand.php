<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Console;

use App\Product\Application\Command\ImportProductsFromCsvCommand;
use App\Product\Application\CommandHandler\ImportProductsFromCsvCommandHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:import-products',
    description: 'Import products from a CSV file (name,description,priceCents,stock,images).',
)]
final class ImportProductsCommand extends Command
{
    public function __construct(
        private ImportProductsFromCsvCommandHandler $importProducts,
        #[Autowire('%kernel.project_dir%/fixtures/products.csv')]
        private string $defaultCsvPath,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('csv', InputArgument::OPTIONAL, 'Path to the products CSV file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csvPath = $input->getArgument('csv') ?? $this->defaultCsvPath;
        $imported = $this->importProducts->handle(new ImportProductsFromCsvCommand((string) $csvPath));

        $io->success(sprintf('Imported %d product(s) from %s.', $imported, $csvPath));

        return Command::SUCCESS;
    }
}
