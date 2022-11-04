<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\I485Entry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<I485Entry>
 *
 * @method I485Entry|null find($id, $lockMode = null, $lockVersion = null)
 * @method I485Entry|null findOneBy(array $criteria, array $orderBy = null)
 * @method I485Entry[]    findAll()
 * @method I485Entry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class I485EntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, I485Entry::class);
    }

    public function save(I485Entry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(I485Entry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array{0: array<string, 1>, 1: array<string, float[]>}
     */
    public function getDataForChart(): array
    {
        $data = [];
        $labels = [];

        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.createdAt');
        /** @var I485Entry[] $result */
        $result = $qb->getQuery()->getResult();
        foreach ($result as $entry) {
            $data[$entry->getProcessingCenter()][] = $entry->getWaitTime();
            $labels[$entry->getCreatedAt()->format('Y-m-d')] = 1;
        }

        return [$labels, $data];
    }
}
