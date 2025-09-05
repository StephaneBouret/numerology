<?php

namespace App\Repository;

use App\Entity\ScheduleSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScheduleSetting>
 */
class ScheduleSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduleSetting::class);
    }

    public function findAllKeyValue(): array
    {
        $qb = $this->createQueryBuilder('s');
        $settings = [];
        foreach ($qb->getQuery()->getResult() as $setting) {
            $settings[$setting->getSettingKey()] = $setting->getValue();
        }
        return $settings;
    }

    //    /**
    //     * @return ScheduleSetting[] Returns an array of ScheduleSetting objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ScheduleSetting
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
