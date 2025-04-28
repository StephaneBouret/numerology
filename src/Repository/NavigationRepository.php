<?php

namespace App\Repository;

use App\Entity\Navigation;
use App\Entity\Program;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Navigation>
 */
class NavigationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Navigation::class);
    }

    public function truncateTable()
    {
        $tableName = $this->getClassMetadata()->getTableName();
        $this->getEntityManager()->getConnection()->executeStatement("TRUNCATE TABLE $tableName");
    }

    public function findAllNames(): array
    {
        return $this->createQueryBuilder('n')
            ->select('n.path')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findByProgram(Program $program): array
    {
        return $this->createQueryBuilder('n')
            ->join('n.course', 'c')
            ->join('c.section', 's')
            ->where('s.program = :program')
            ->setParameter('program', $program)
            ->orderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Navigation[] Returns an array of Navigation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Navigation
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
