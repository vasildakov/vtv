<?php

declare(strict_types=1);

namespace VTV\Command;

use Redis;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Laminas\Diagnostics\Check;
use Laminas\Diagnostics\Result;
use Laminas\Diagnostics\Runner\Runner;
use VTV\Diagnostics\Reporter\VerboseConsole;

/**
 * Class DiagnosticsCommand
 *
 * @author Vasil Dakov <vasil.dakov@digitaspixelpark.com>
 */
class DiagnosticsCommand extends Command
{
    /**
     * @var array $extensions
     */
    private array $extensions = [
        'redis',
        'xml',
        'bcmath',
        'intl',
        'zip',
        'Zend OPcache'
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('vtv:diagnostics')
            ->setDescription('Performing application diagnostic tests')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Create Runner instance
        $runner = new Runner();

        // CPU Performance
        $runner->addCheck(new Check\CpuPerformance(0.5)); // at least 50% of EC2 micro instance

        // PHP version and extensions
        $runner->addCheck(new Check\PhpVersion('8.1', '>'));
        $runner->addCheck(new Check\ExtensionLoaded($this->extensions));
        $runner->addCheck(new Check\SecurityAdvisory('composer.lock'));

        // Disk and directories
        $runner->addCheck(new Check\DirWritable('./data/cache'));
        $runner->addCheck(new Check\DiskFree(100000000, './data'));

        // Classes
        $runner->addCheck(new Check\ClassExists([Redis::class, Pheanstalk::class]));

        // Web Server
        $http = new Check\HttpService('localhost');
        $http->setLabel('Web Server is working.');
        $runner->addCheck($http);

        // Redis
        $redis = new Check\Redis($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
        $redis->setLabel('Redis is working.');
        $runner->addCheck($redis);

        // Beanstalk
        $beanstalk = new Check\Callback(function () {
            $fp = \pfsockopen( (string) $_ENV['BEANSTALK_HOST'], (int) $_ENV['BEANSTALK_PORT']);
            if (!$fp) {
                return new Result\Failure('Beanstalk is not working');
            }
            return new Result\Success('Beanstalk is working');
        });
        $runner->addCheck($beanstalk);


        // Add console reporter
        $console = \Laminas\Console\Console::getInstance();
        $runner->addReporter(new VerboseConsole($console));

        // Run all checks
        $results = $runner->run();

        // exit($status);
        return ($results->getFailureCount() + $results->getWarningCount()) > 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
