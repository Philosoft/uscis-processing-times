<?php

declare(strict_types=1);

namespace App\Command;

use App\Dto\ApiResponse;
use App\Dto\Partial\Subtype;
use App\Entity\I140Entry;
use App\Repository\I140EntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\BrowserKit\Exception\BadMethodCallException;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:crawl:i140',
    description: 'Add a short description for your command',
)]
class CrawlI140Command extends Command
{
    private const PROCESSING_CENTER_TO_ENDPOINT = [
        'texas' => 'https://egov.uscis.gov/processing-times/api/processingtime/I-140/SSC/136A-NIW',
        'nebraska' => 'https://egov.uscis.gov/processing-times/api/processingtime/I-140/NSC/136A-NIW',
    ];

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly I140EntryRepository $entryRepository
    ) {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $browser = new HttpBrowser(
                HttpClient::create([
                    'verify_peer' => false,
                ])
            );
        } catch (\LogicException $e) {
            $io->error($e->getMessage());
            return self::FAILURE;
        }

        $browser->request('GET', 'https://egov.uscis.gov/processing-times');

        $times = [];
        foreach (self::PROCESSING_CENTER_TO_ENDPOINT as $center => $url) {
            $io->info("Working on {$center}");
            $browser->request('GET', $url);
            try {
                /** @var Response $response */
                $response = $browser->getResponse();
            } catch (BadMethodCallException $e) {
                $io->error($e->getMessage());
                continue;
            }

            $jsonString = $response->getContent();
            /** @var ApiResponse $apiResponse */
            $apiResponse = $this->serializer->deserialize($jsonString, ApiResponse::class, 'json');
            /** @var Subtype[] $subtypes */
            $subtypes = $apiResponse->data->processingTime->subtypes;
            if (count($subtypes) > 0) {
                $primarySubtype = $subtypes[0];
                $ranges = $primarySubtype->range;
                if (count($ranges) > 0) {
                    $waitTime = $ranges[1]->value ?? $ranges[0]->value ?? -1.0;
                    $times[] = [$center, $waitTime];

                    $entry = $this->entryRepository->findOneBy([
                        'processingCenter' => $center,
                        'createdAt' => new \DateTimeImmutable(),
                    ]);
                    if ($entry !== null) {
                        continue;
                    }

                    $entry = new I140Entry();
                    $entry->setProcessingCenter($center);
                    $entry->setRawResponse($jsonString);
                    $entry->setWaitTime($waitTime);

                    $this->entityManager->persist($entry);
                }
            }
        }

        $this->entityManager->flush();

        $io->table(['center', 'time'], $times);

        return Command::SUCCESS;
    }
}
