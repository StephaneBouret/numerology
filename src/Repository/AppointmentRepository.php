<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\AppointmentType;
use App\Entity\User;
use App\Enum\AppointmentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function hasUserCompletedType(User $user, AppointmentType $type): bool
    {
        return (bool) $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.type = :type')
            ->andWhere('a.status IN (:status)')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('status', [
                AppointmentStatus::CONFIRMED
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Appointment[] Returns an array of Appointment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Appointment
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
