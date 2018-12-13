<?php
namespace Borraz\Command;

use Borraz\Datafeed;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshDevFeedData extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:refresh-feed';

    protected function configure()
    {
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Refreshes the developer\'s feed data.')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to get the latest developer\'s feed data. It does not take inputs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '',
            '============',
            'Refreshing data',
            '',
        ]);
        $dataFeed =  new Datafeed();
        $dataFeed->refresh();
        $output->writeln([
            '',
            'Refresh complete!',
            '============',
            '',
        ]);
    }
}