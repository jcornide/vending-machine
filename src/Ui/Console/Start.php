<?php
namespace App\Ui\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Start extends Command
{
    protected static $defaultName = 'start';

    protected function configure(): void
    {
        $this
            ->setDescription('Start the vending machine app')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Welcome to the Vending Machine");
        return Command::SUCCESS;
    }
}
