<?php

declare(strict_types=1);

namespace VTV;

use GuzzleHttp\ClientInterface;
use Laminas\ServiceManager\ServiceManager;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerInterface;
use Redis;
use Symfony\Component\Console\Application;
use VTV\Command\ProducerCreateJobs;
use VTV\Command\ProducerDeleteCache;
use VTV\Command\WorkerDispatchJob;
use VTV\Command\DiagnosticsCommand;

$container = new ServiceManager([
    'factories' => [
        Application::class => function (ContainerInterface $container): Application {
            return new Application('VTV Data Import Application', '1.0');
        },
        ClientInterface::class => function (ContainerInterface $container): ClientInterface {
            return new \GuzzleHttp\Client([
                'base_uri' => $_ENV['API_BASE_URI'],
                'timeout'  => 60,
            ]);
        },
        Logger::class => function (ContainerInterface $container): Logger {
            return new Logger('default');
        },
        Redis::class => function(ContainerInterface $container): Redis {
            $redis = new Redis();
            $redis->connect((string) $_ENV['REDIS_HOST'], (int) $_ENV['REDIS_PORT']);

            return $redis;
        },
        Pheanstalk::class => function (ContainerInterface $container): PheanstalkInterface {
            return Pheanstalk::create(
                (string) $_ENV['BEANSTALK_HOST'],
                (int) $_ENV['BEANSTALK_PORT']
            );
        },
        DiagnosticsCommand::class=> function (ContainerInterface $container): DiagnosticsCommand {
            return new DiagnosticsCommand();
        },
        WorkerDispatchJob::class => function (ContainerInterface $container): WorkerDispatchJob {
            $pheanstalk = $container->get(Pheanstalk::class);
            $client = $container->get(ClientInterface::class);
            $redis = $container->get(Redis::class);

            $logger = new Logger('worker');
            $logger->pushHandler(new StreamHandler('./data/logs/worker.log', Level::Info));

            return new WorkerDispatchJob($pheanstalk, $logger, $client, $redis);
        },
        ProducerCreateJobs::class => function (ContainerInterface $container): ProducerCreateJobs {
            $pheanstalk = $container->get(Pheanstalk::class);
            $redis = $container->get(Redis::class);
            $logger = new Logger('producer');
            $logger->pushHandler(new StreamHandler('./data/logs/producer.log', Level::Info));

            return new ProducerCreateJobs($pheanstalk, $logger, $redis);
        },
        ProducerDeleteCache::class => function (ContainerInterface $container): ProducerDeleteCache {
            return new ProducerDeleteCache(
                $container->get(Redis::class)
            );
        },
    ],
]);

return $container;