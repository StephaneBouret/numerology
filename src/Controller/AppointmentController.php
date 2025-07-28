<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AppointmentController extends AbstractController
{
    #[Route('/rendezvous', name: 'app_appointment')]
    public function book(Request $request, EntityManagerInterface $em): Response
    {
        $appointment = new Appointment();

        $form = $this->createForm(AppointmentFormType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appointment->setUser($this->getUser());

            // Calcul automatique de de la date de fin
            $startAt = $appointment->getStartAt();
            $duration = $appointment->getType()->getDuration(); // durée en minutes
            $endAt = (clone $startAt)->modify("+{$duration} minutes");
            $appointment->setEndAt($endAt);
            $em->persist($appointment);
            $em->flush();

            // Prévoir redirection vers Stripe ou page de confirmation
            return $this->redirectToRoute('app_appointment_confirmation');
        }

        return $this->render('appointment/book.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/rendezvous/confirmation', name: 'app_appointment_confirmation')]
    public function confirmation(): Response
    {
        return $this->render('appointment/confirmation.html.twig');
    }
}
