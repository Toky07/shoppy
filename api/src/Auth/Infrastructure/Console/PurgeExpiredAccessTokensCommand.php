<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Console;

use App\Auth\Application\Command\PurgeExpiredAccessTokensCommand as PurgeExpiredAccessTokens;
use App\Auth\Application\CommandHandler\PurgeExpiredAccessTokensCommandHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:purge-expired-tokens',
    description: 'Delete expired access and refresh session tokens.',
)]
final class PurgeExpiredAccessTokensCommand extends Command
{
    public function __construct(private PurgeExpiredAccessTokensCommandHandler $purgeExpiredAccessTokens)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $deleted = $this->purgeExpiredAccessTokens->handle(new PurgeExpiredAccessTokens());

        (new SymfonyStyle($input, $output))->success(sprintf('Deleted %d expired token(s).', $deleted));

        return Command::SUCCESS;
    }
}
