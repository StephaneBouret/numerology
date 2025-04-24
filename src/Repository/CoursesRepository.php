<?php

namespace App\Repository;

use App\Entity\Courses;
use App\Entity\Program;
use App\Entity\Sections;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Courses>
 */
class CoursesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Courses::class);
    }

    public function countNumberCoursesBySection(Sections $sections): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->andWhere('c.section = :sections')
            ->setParameter('sections', $sections)
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }

    public function countAll(): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }

    public function countByProgram(Program $program): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->join('c.section', 's')
            ->where('s.program = :program')
            ->setParameter('program', $program)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCoursesBySections(): array
    {
        return $this->createQueryBuilder('c')
            ->select('s.name AS section_name, COUNT(c.id) AS course_count')
            ->join('c.section', 's')
            ->groupBy('s.id')
            ->getQuery()
            ->getResult();
    }

    public function findAllNames(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.slug')
            ->getQuery()
            ->getSingleColumnResult();
    }

    //    /**
    //     * @return Courses[] Returns an array of Courses objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Courses
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
