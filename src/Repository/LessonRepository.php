<?php

namespace App\Repository;

use App\Entity\Lesson;
use App\Entity\Program;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    public function countLessonsDoneByUser($user): int
    {
        return $this->createQueryBuilder('l')
            ->select('count(l.id)')
            ->andWhere('l.user = :val')
            ->andWhere('l.status = :value')
            ->setParameter('val', $user)
            ->setParameter('value', 'DONE')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countLessonsDoneByUserGroupedByProgram($user): array
    {
        $qb = $this->createQueryBuilder('l')
            ->select('IDENTITY(c.program) AS programId, COUNT(l.id) AS lessonsDone')
            ->join('l.courses', 'c')
            ->andWhere('l.user = :user')
            ->andWhere('l.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'DONE')
            ->groupBy('programId');

        $results = $qb->getQuery()->getResult();

        $mapped = [];
        foreach ($results as $row) {
            $mapped[$row['programId']] = (int) $row['lessonsDone'];
        }

        return $mapped;
    }

    public function countLessonsDoneByUserAndProgram($user, Program $program): int
    {
        return $this->createQueryBuilder('l')
            ->select('count(l.id)')
            ->innerJoin('l.courses', 'c')
            ->innerJoin('c.section', 's')
            ->andWhere('l.user = :user')
            ->andWhere('l.status = :status')
            ->andWhere('s.program = :program')
            ->setParameter('user', $user)
            ->setParameter('status', 'DONE')
            ->setParameter('program', $program)
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
