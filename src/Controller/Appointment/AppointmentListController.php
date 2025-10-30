<?php

namespace App\Controller\Appointment;

use App\Entity\User;
use App\Enum\AppointmentStatus;
use App\Repository\AppointmentRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

#[Route('/rendez-vous')]
final class AppointmentListController extends AbstractController
{
    #[Route('/list', name: 'app_appointment_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page')]
    public function index(AppointmentRepository $repo): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $appointments = $repo->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.status IN (:statuses)')
            ->andWhere('a.number IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('statuses', [AppointmentStatus::CONFIRMED, AppointmentStatus::CANCELED])
            ->orderBy('a.startAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('appointment/list.html.twig', [
            'appointments' => $appointments,
        ]);
    }
}

