<?php

declare(strict_types=1);

namespace VTV\Command;

use Redis;
use RedisException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ProducerDeleteCache
 *
 * @author Vasil Dakov <vasil.dakov@digitaspixelpark.com>
 */
class ProducerDeleteCache extends Command
{
    public function __construct(private Redis $redis)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('vtv:producer:delete-cache')
            ->setDescription('Drop the producer cache')
            ->setHelp('Drop the producer cache')
        ;
        parent::configure();
    }

    /**
     * @throws RedisException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $keys  = $this->redis->keys('producer:*');
        $count = count($keys);
        if (!$count) {
            $output->writeln("<fg=red>There is no producer cache.</>");
            return Command::INVALID;
        }

        $progressBar = new ProgressBar($output, $count);
        $progressBar->setBarCharacter('<fg=green>☰</>');
        $progressBar->setEmptyBarCharacter("<fg=gray>☰</>");
        $progressBar->setProgressCharacter("<fg=blue>☰</>");

        $progressBar->start();
        foreach ($keys as $key) {
            $progressBar->advance();
            usleep(1000000); // sleep a little bit
            $this->redis->del($key);
        }
        $progressBar->finish();

        $io->newLine();

        $output->writeln("<fg=green>The producer cache has been deleted successfully!</>");

        return Command::SUCCESS;
    }
}
