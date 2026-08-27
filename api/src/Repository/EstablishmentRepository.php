<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Establishment;
use App\Entity\Evaluation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Establishment>
 */
class EstablishmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Establishment::class);
    }

    public function setAllEvaluationsStatus(Uuid $id, bool $status): void
    {
        $this->createQueryBuilder('e')
            ->update(Evaluation::class, 'e')
            ->set('e.active', ':status')
            ->where('e.establishment = :id')
            ->setParameter('id', $id)
            ->setParameter('status', $status)
            ->getQuery()
            ->execute();
    }

    //    /**
    //     * @return Establishments[] Returns an array of Establishments objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Establishments
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
