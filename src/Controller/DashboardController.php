<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\I140EntryRepository;
use App\Repository\I485EntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app__home')]
    public function index(
        I140EntryRepository $entryRepository,
        I485EntryRepository $i485EntryRepository,
        ChartBuilderInterface $chartBuilder
    ): Response {
        [$labels, $data] = $entryRepository->getDataForChart();

        // mainline colors from elementary os palette @see https://elementary.io/brand
        $flatColors = [
            "#c6262e", // "Strawberry 500"
            "#f37329", // "Orange 500"
            "#f9c440", // "Banana 500"
            "#68b723", // "Lime 500"
            "#28bca3", // "Mint 500"
            "#3689e6", // "Blueberry 500"
            "#a56de2", // "Grape 500"
            "#de3e80", // "Bubblegum 500"
        ];

        $datasets = [];
        $colorIterator = -1;
        foreach ($data as $center => $series) {
            $colorIterator++;
            $datasets[] = [
                'label' => $center,
                'data' => $series,
                'borderColor' => $flatColors[$colorIterator % count($flatColors)],
                'backgroundColor' => 'rgb(255, 255, 255)',
            ];
        }

        $i140Chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $i140Chart->setData([
            'labels' => array_keys($labels),
            'datasets' => $datasets,
        ]);

        [$labels, $data] = $i485EntryRepository->getDataForChart();

        $datasets = [];
        $colorIterator = -1;
        foreach ($data as $center => $series) {
            $colorIterator++;
            $datasets[] = [
                'label' => $center,
                'data' => $series,
                'borderColor' => $flatColors[$colorIterator % count($flatColors)],
                'backgroundColor' => 'rgb(255, 255, 255)',
            ];
        }

        $i485Chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $i485Chart->setData([
            'labels' => array_keys($labels),
            'datasets' => $datasets,
        ]);

        return $this->render('dashboard/index.html.twig', [
            'i140Chart' => $i140Chart,
            'i140Entries' => $entryRepository->findBy(
                [
                    'createdAt' => new \DateTimeImmutable(),
                ],
                [
                    'waitTime' => 'ASC',
                ]
            ),
            'i485Chart' => $i485Chart,
            'i485Entries' => $i485EntryRepository->findBy(
                [
                    'createdAt' => new \DateTimeImmutable(),
                ],
                [
                    'waitTime' => 'ASC',
                ]
            ),
        ]);
    }
}
