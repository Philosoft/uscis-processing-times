<?php

declare(strict_types=1);

namespace App\Command;

use App\Dto\ApiResponse;
use App\Dto\FormOfficesApiResponse;
use App\Dto\Partial\FormOffices\Office;
use App\Dto\Partial\Subtype;
use App\Entity\I485Entry;
use App\Repository\I485EntryRepository;
use DateTimeImmutable;
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
    name: 'app:crawl:i485',
    description: 'Add a short description for your command',
)]
class CrawlI485Command extends Command
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly I485EntryRepository $entryRepository
    ) {
        parent::__construct();
    }

    /**
     * @throws BadMethodCallException
     */
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

        $io->info('Navigate to initial page: https://egov.uscis.gov/processing-times');
        $browser->request('GET', 'https://egov.uscis.gov/processing-times');

        $checkResponse = static function (Response $response) use ($io): bool {
            if ($response->getStatusCode() !== 200) {
                $io->error("Get non 200 response code: {$response->getStatusCode()}");
                $io->title('Response headers');
                $io->table(
                    ['header', 'value'],
                    array_map(
                        static fn ($h, $v) => [$h, implode("\n", $v)],
                        array_keys($response->getHeaders()),
                        array_values($response->getHeaders())
                    )
                );

                $io->title('Response body');
                $io->text($response->getContent());

                return false;
            }

            return true;
        };

        /** @var Response $response */
        $response = $browser->getResponse();
        if (!$checkResponse($response)) {
            return self::FAILURE;
        }

        $io->info('Query for processing centers');
        $browser->request('GET', 'https://egov.uscis.gov/processing-times/api/formoffices/I-485/131A');

        /** @var Response $response */
        $response = $browser->getResponse();
        if (!$checkResponse($response)) {
            return self::FAILURE;
        }

        /** @var FormOfficesApiResponse $apiResponse */
        $apiResponse = $this->serializer->deserialize($response->getContent(), FormOfficesApiResponse::class, 'json');
        /** @var Office[] $offices */
        $offices = $apiResponse->data->wrapper->offices;
        $codeToDescription = [];
        foreach ($offices as $office) {
            $codeToDescription[$office->code] = $office->description;
        }

        $times = [];
        $io->progressStart(count($offices));
        foreach ($offices as $office) {
            /** @noinspection DisconnectedForeachInstructionInspection */
            $io->progressAdvance();

            $url = "https://egov.uscis.gov/processing-times/api/processingtime/I-485/{$office->code}/131A";

            $today = new DateTimeImmutable();
            $browser->request('GET', $url);
            try {
                /** @var Response $response */
                $response = $browser->getResponse();
            } catch (BadMethodCallException $e) {
                $io->error($e->getMessage());
                continue;
            }

            /** @var ApiResponse $apiResponse */
            $content = $response->getContent();
            $apiResponse = $this->serializer->deserialize($content, ApiResponse::class, 'json');

            /** @var Subtype[] $subtypes */
            $subtypes = $apiResponse->data->processingTime->subtypes;
            if (count($subtypes) > 0) {
                $primarySubtype = $subtypes[0];
                $ranges = $primarySubtype->range;
                if (count($ranges) > 0) {
                    $officeCode = $apiResponse->data->processingTime->officeCode;
                    $center = $codeToDescription[$officeCode];
                    $waitTime = $ranges[1]->value ?? $ranges[0]->value ?? -1.0;
                    $waitTime *= match (strtolower($ranges[1]->unit ?? $ranges[0]->unit ?? 'months')) {
                        'years' => 12,
                        'days' => 0.33,
                        default => 1
                    };

                    $entry = $this->entryRepository->findOneBy([
                        'officeCode' => $officeCode,
                        'createdAt' => $today,
                    ]);
                    if ($entry !== null) {
                        continue;
                    }

                    $entry = new I485Entry();
                    $entry->setProcessingCenter($center);
                    $entry->setOfficeCode($officeCode);
                    $entry->setRawResponse($content);
                    $entry->setWaitTime($waitTime);
                    $entry->setPublicationDate(new DateTimeImmutable($primarySubtype->publicationDate));
                    $entry->setServiceRequestDate(new DateTimeImmutable($primarySubtype->serviceRequestDate));

                    $this->entityManager->persist($entry);
                    $times[] = [$center, $waitTime];
                }
            }
        }

        $this->entityManager->flush();

        $io->table(['Center', 'Waiting time'], $times);

        return Command::SUCCESS;
    }
}
