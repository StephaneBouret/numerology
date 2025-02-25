<?php

namespace App\Repository;

use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lesson>
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    /**
     * Trouve la leçon correspondant à l'utilisateur connecté et au cours choisi
     *
     * @return Lesson|null
     */
    public function getLessonByUserByCourse($user, $course): ?Lesson
    {
        return $this->createQueryBuilder('l')
        ->andWhere('l.user = :val')
        ->andWhere('l.courses = :value')
        ->setParameter('val', $user)
        ->setParameter('value', $course)
        ->orderBy('l.id', 'ASC')
        ->getQuery()
        ->getOneOrNullResult();
    }

    public function countLessonsDoneByUser($id): int
    {
        return $this->createQueryBuilder('l')
            ->select('count(l.id)')
            ->andWhere('l.user = :val')
            ->andWhere('l.status = :value')
            ->setParameter('val', $id)
            ->setParameter('value', 'DONE')
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return Lesson[] Returns an array of Lesson objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Lesson
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
