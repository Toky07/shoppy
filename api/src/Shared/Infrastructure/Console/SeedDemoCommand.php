<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Console;

use App\Product\Application\Command\ImportProductsFromCsvCommand;
use App\Product\Application\CommandHandler\ImportProductsFromCsvCommandHandler;
use App\User\Application\Command\SeedDemoUsersCommand;
use App\User\Application\CommandHandler\SeedDemoUsersCommandHandler;
use App\User\Application\DemoAccounts;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\ORM\EntityManagerInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        private EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/fixtures/products.csv')]
        private string $productsCsvPath,
        #[Autowire('%app.media.upload_dir%')]
        private string $uploadDirectory,
        #[Autowire('%kernel.environment%')]
        private string $environment,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'reset',
            null,
            InputOption::VALUE_NONE,
            'Drop the database, rerun migrations, and clear uploaded files.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->environment === 'prod') {
            $io->error('Demo accounts cannot be seeded in production.');

            return Command::FAILURE;
        }

        if ($input->getOption('reset') === true) {
            $reset = $this->resetStore($output);
            if ($reset !== Command::SUCCESS) {
                $io->error('Unable to reset the database before seeding.');

                return $reset;
            }
        }

        $users = $this->seedDemoUsers->handle(new SeedDemoUsersCommand());
        $products = $this->importProducts->handle(new ImportProductsFromCsvCommand($this->productsCsvPath));

        $io->success(sprintf('Seeded %d user(s) and imported %d product(s).', $users, $products));
        $io->listing([
            sprintf('Admin: %s / %s', DemoAccounts::ADMIN_EMAIL, DemoAccounts::PASSWORD),
            sprintf('Visiteur: %s / %s', DemoAccounts::VISITOR_EMAIL, DemoAccounts::PASSWORD),
            sprintf('Images: %s', $this->uploadDirectory),
        ]);

        return Command::SUCCESS;
    }

    private function resetStore(OutputInterface $output): int
    {
        $application = $this->getApplication();
        if ($application === null) {
            return Command::FAILURE;
        }

        $application->setAutoExit(false);
        $connection = $this->entityManager->getConnection();

        if ($connection->getDatabasePlatform() instanceof SQLitePlatform) {
            $path = $connection->getParams()['path'] ?? null;
            $connection->close();

            if (is_string($path) && $path !== '' && is_file($path)) {
                unlink($path);
            }
        } else {
            foreach ([
                ['command' => 'doctrine:database:drop', '--force' => true, '--if-exists' => true, '--no-interaction' => true],
                ['command' => 'doctrine:database:create', '--no-interaction' => true],
            ] as $arguments) {
                $code = $application->run(new ArrayInput($arguments), $output);
                if ($code !== Command::SUCCESS) {
                    return $code;
                }
            }
        }

        $migrate = $application->run(new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
        ]), $output);

        if ($migrate !== Command::SUCCESS) {
            return $migrate;
        }

        $this->emptyDirectory($this->uploadDirectory);

        return Command::SUCCESS;
    }

    private function emptyDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            $pathname = $file->getPathname();
            $file->isDir() ? rmdir($pathname) : unlink($pathname);
        }
    }
}
