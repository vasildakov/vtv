<?php

declare(strict_types=1);

namespace VTV\Command;

use Exception;
use GuzzleHttp\ClientInterface;
use Laminas\Json\Json;
use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Job;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Redis;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function base64_encode;
use function file_get_contents;

/**
 * Class WorkerDispatchJob
 *
 * @author Vasil Dakov <vasil.dakov@digitaspixelpark.com>
 */
class WorkerDispatchJob extends Command
{
    public function __construct(
        private PheanstalkInterface $pheanstalk,
        private LoggerInterface $logger,
        private ClientInterface $client,
        private Redis $redis
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('vtv:worker:start')
            ->setDescription('The worker sends HTTP requests to the API endpoint')
            ->setHelp('')
        ;
        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->pheanstalk->watch($_ENV['BEANSTALK_TUBE']);

        while (true) {
            // this hangs until a Job is produced.
            $job = $this->pheanstalk->reserve();
            if (!$job instanceof Job) continue;

            try {
                $data = Json::decode($job->getData(),Json::TYPE_ARRAY);

                $data['pdfFile'] = base64_encode(file_get_contents('./data/xml/'.$data['pdfFile']));

                // do work.
                $response = $this->client->request('PUT', 'http://vtv_api:80/wage-agreement', [
                    'body' => Json::encode($data)
                ]);

                if (200 !== $response->getStatusCode()) {
                    throw new Exception('API Endpoint returns error');
                }

                $this->redis->set("worker:{$data['id']}", $job->getData());
                $output->writeln("<fg=green>Key added to redis</>");

                $this->logger->log(LogLevel::INFO,'SUCCESS: Job has been executed', [
                    'job' => $job->getId(),
                    'request' => [
                        'document' => [
                            'id' => $data['id']
                        ]
                    ],
                    'response' => [
                        'statusCode' => $response->getStatusCode()
                    ]
                ]);

                $output->writeln("<fg=green>Job {$job->getId()} has been executed successfully</>");

                // eventually we're done, delete job.
                $this->pheanstalk->delete($job);

            } catch (Throwable $e) {
                $this->logger->log(LogLevel::ERROR, 'ERROR: Job has not been executed', [
                    'job' => $job->getId(),
                    'exception' => $e->getMessage()
                ]);

                $output->writeln('<error>ERROR: Job has not been executed</error>');

                // handle exception.
                // and let some other worker retry.
                $this->pheanstalk->release($job);
            }
        }
    }
}
