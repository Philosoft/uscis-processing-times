<?php

declare(strict_types=1);

namespace App\Command;

use App\Dto\ApiResponse;
use App\Dto\Partial\Subtype;
use App\Entity\I485Entry;
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
    private const PROCESSING_CENTER_TO_ENDPOINT = [
        "California Service Center" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/CSC/131A",
        "Nebraska Service Center" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/NSC/131A",
        "Texas Service Center" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SSC/131A",
        "Agana GU" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/AGA/131A",
        "Albany NY" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/ALB/131A",
        "Albuquerque NM" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/ABQ/131A",
        "Anchorage AK" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/ANC/131A",
        "Atlanta GA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/ATL/131A",
        "Baltimore MD" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/BAL/131A",
        "Boise ID" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/BOI/131A",
        "Boston MA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/BOS/131A",
        "Brooklyn NY" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/BNY/131A",
        "Buffalo NY" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/BUF/131A",
        "Burlington VT" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/STA/131A",
        "Charleston SC" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/CHL/131A",
        "Charlotte Amalie VI" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/CHA/131A",
        "Charlotte NC" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/CLT/131A",
        "Chicago IL" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/CHI/131A",
        "Christiansted VI" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/CHR/131A",
        "Cincinnati OH" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/CIN/131A",
        "Cleveland OH" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/CLE/131A",
        "Columbus OH" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/CLM/131A",
        "Dallas TX" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/DAL/131A",
        "Denver CO" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/DEN/131A",
        "Des Moines IA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/DSM/131A",
        "Detroit MI" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/DET/131A",
        "El Paso TX" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/ELP/131A",
        "Fort Myers FL" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/OFM/131A",
        "Fort Smith AR" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/FSA/131A",
        "Fresno CA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/FRE/131A",
        "Greer SC" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/GRR/131A",
        "Harlingen TX" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/HLG/131A",
        "Hartford CT" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/HAR/131A",
        "Helena MT" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/HEL/131A",
        "Hialeah FL" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/HIA/131A",
        "Honolulu HI" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/HHW/131A",
        "Houston TX" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/HOU/131A",
        "Imperial CA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/IMP/131A",
        "Indianapolis IN" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/INP/131A",
        "Jacksonville FL" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/JAC/131A",
        "Kansas City MO" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/KAN/131A",
        "Kendall FL" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/KND/131A",
        "Las Vegas NV" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/LVG/131A",
        "Lawrence MA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/LAW/131A",
        "Long Island NY" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/LNY/131A",
        "Los Angeles CA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/LOS/131A",
        "Los Angeles County CA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/LAC/131A",
        "Louisville KY" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/LOU/131A",
        "Manchester NH" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/MAN/131A",
        "Memphis TN" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/MEM/131A",
        "Miami FL" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/MIA/131A",
        "Milwaukee WI" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/MIL/131A",
        "Minneapolis-St. Paul MN" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SPM/131A",
        "Montgomery AL" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/MGA/131A",
        "Mount Laurel NJ" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/MTL/131A",
        "Nashville TN" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/NTN/131A",
        "Newark NJ" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/NEW/131A",
        "New Orleans LA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/NOL/131A",
        "New York City NY" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/NYC/131A",
        "Norfolk VA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/NOR/131A",
        "Oakland Park FL" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/OKL/131A",
        "Oklahoma City OK" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/OKC/131A",
        "Omaha NE" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/OMA/131A",
        "Orlando FL" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/ORL/131A",
        "Philadelphia PA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/PHI/131A",
        "Phoenix AZ" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/PHO/131A",
        "Pittsburgh PA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/PIT/131A",
        "Portland ME" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/POM/131A",
        "Portland OR" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/POO/131A",
        "Providence RI" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/PRO/131A",
        "Queens NY" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/QNS/131A",
        "Raleigh NC" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/RAL/131A",
        "Reno NV" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/REN/131A",
        "Sacramento CA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SAC/131A",
        "Saint Louis MO" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/STL/131A",
        "Salt Lake City UT" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SLC/131A",
        "San Antonio TX" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SNA/131A",
        "San Bernardino CA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SBD/131A",
        "San Diego CA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SND/131A",
        "San Fernando Valley CA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SFV/131A",
        "San Francisco CA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SFR/131A",
        "San Jose CA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SNJ/131A",
        "San Juan PR" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SAJ/131A",
        "Santa Ana CA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SAA/131A",
        "Seattle WA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SEA/131A",
        "Spokane WA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/SPO/131A",
        "Tampa FL" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/TAM/131A",
        "Tucson AZ" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/TUC/131A",
        "Washington DC" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/WAS/131A",
        "West Palm Beach FL" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/WPB/131A",
        "Wichita KS" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/WIC/131A",
        "Yakima WA" => "https://egov.uscis.gov/processing-times/api/processingtime/I-485/YAK/131A",
    ];

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager
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

        $data = [];
        $io->progressStart(count(self::PROCESSING_CENTER_TO_ENDPOINT));
        foreach (self::PROCESSING_CENTER_TO_ENDPOINT as $center => $url) {
            $io->progressAdvance();
            $browser->request('GET', $url);
            try {
                /** @var Response $response */
                $response = $browser->getResponse();
            } catch (BadMethodCallException $e) {
                $io->error($e->getMessage());
                continue;
            }
            $data[$center] = $response->getContent();
        }

        $times = [];
        foreach ($data as $center => $jsonString) {
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

                    $entry = new I485Entry();
                    $entry->setProcessingCenter($center);
                    $entry->setRawResponse($jsonString);
                    $entry->setWaitTime($waitTime);
                    $entry->setPublicationDate(new DateTimeImmutable($primarySubtype->publicationDate));
                    $entry->setServiceRequestDate(new DateTimeImmutable($primarySubtype->serviceRequestDate));

                    $this->entityManager->persist($entry);
                }
            }
        }

        $this->entityManager->flush();

        $io->table(['center', 'time'], $times);

        return Command::SUCCESS;
    }
}
