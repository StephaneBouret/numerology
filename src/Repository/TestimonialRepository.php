<?php

namespace App\Repository;

use App\Entity\Testimonial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Testimonial>
 */
class TestimonialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Testimonial::class);
    }

    /**
     * Calcule la moyenne des notes des témoignages
     *
     * @return float
     */
    public function getAvgRatings(): float
    {
        $avgRating = $this->createQueryBuilder('t')
            ->select('AVG(t.rating) as avgRating')
            ->where('t.isApproved = true')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) $avgRating;
    }

    /**
     * Obtient le nombre total de témoignages
     *
     * @return integer
     */
    public function getTotalCount(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.isApproved = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return Testimonial[] Returns an array of Testimonial objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Testimonial
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
