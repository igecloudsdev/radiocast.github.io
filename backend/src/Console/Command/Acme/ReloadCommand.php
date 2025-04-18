<?php

declare(strict_types=1);

namespace App\Console\Command\Acme;

use App\Console\Command\CommandAbstract;
use App\Service\Acme;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'azuracast:acme:reload',
    description: 'Reload certificate-using services (in case of manual HTTPS cert update).',
    aliases: ['acme:reload']
)]
final class ReloadCommand extends CommandAbstract
{
    public function __construct(
        private readonly Acme $acme
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->acme->reloadServices();
        } catch (Exception $e) {
            $io->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
