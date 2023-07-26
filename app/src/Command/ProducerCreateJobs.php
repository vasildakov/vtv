<?php

declare(strict_types=1);

namespace VTV\Command;

use Generator;
use Iterator;
use Laminas\Json\Json;
use Mtownsend\XmlToArray\XmlToArray;
use Pheanstalk\Contract\PheanstalkInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Redis;
use RedisException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use VTV\Model\Document;

/**
 * Class ProducerCreateJobs
 *
 * @author Vasil Dakov <vasil.dakov@digitaspixelpark.com>
 */
class ProducerCreateJobs extends Command
{
    private const DELAY = 30;

    private const TTR   = 60;

    public function __construct(
        private PheanstalkInterface $pheanstalk,
        private LoggerInterface $logger,
        private Redis $redis
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('vtv:producer:create-jobs')
            ->setDescription('The producer creates and put jobs in the queue')
            ->setHelp('The producer creates jobs that will be processed by the worker')
        ;
        parent::configure();
    }


    private function recursiveDirectoryIterator(string $path = ''): Iterator|Generator
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() != 'xml') continue;

            yield $file;
        }
    }

    /**
     * @throws RedisException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $start = microtime(true);
        $totalFiles = 0;

        $iterator = $this->recursiveDirectoryIterator('./data/xml/');
        foreach($iterator as $file) {
            # remove the extension from the filename
            $id = substr($file->getFilename(), 0, strrpos($file->getFilename(), '.'));

            # check if job is already produced for this file
            if ( $this->redis->exists("producer:{$id}") ) {
                continue;
            }

            # read and parse the xml file
            $xml   = file_get_contents($file->getPathname());
            $array = XmlToArray::convert($xml);

            $pdf     = $array['publicationFile']['@attributes']['href'];
            $title   = $array['title']['div']['p'];
            $updated = $array['dateOfModification']['@content'];
            $created = $array['dateOfIssue']['@content'];

            $temps = $array['cl2Categories']['classifiedlinklists']['classifiedlinklist'];
            $states = [];
            foreach ($temps as $key => $value) {
                if (isset($value['@attributes'])) {
                    $state = explode('/', $value['@attributes']['path']);
                    $states[] = end($state);
                }
            }

            // create a new document
            $document = new Document(
                id: $id,
                title: $title,
                pdfFile: $pdf,
                publicationDate: $created,
                states: $states,
            );

            $this->pheanstalk->useTube($_ENV['BEANSTALK_TUBE']);
            // put the job in the queue
            $this->pheanstalk->put(
                Json::encode($document->toArray()),
                PheanstalkInterface::DEFAULT_PRIORITY,
                self::DELAY,
                self::TTR
            );

            $message = "Job {$id} has been produced";

            $output->writeln("<fg=green>{$message}</>");

            $this->redis->set("producer:{$id}", Json::encode($document->toArray()));

            // create log
            $this->logger->log(LogLevel::INFO, $message, $document->toArray());
            $totalFiles++;
        }

        $io->newLine();

        $output->writeln('<fg=green>Memory peak usage: '.(memory_get_peak_usage(true)/1024/1024).' MB</>');
        $output->writeln('<fg=green>Total number of files: '.$totalFiles.'</>');
        $output->writeln('<fg=green>Completed in: '.(microtime(true) - $start).' seconds</>');

        return Command::SUCCESS;
    }
}
