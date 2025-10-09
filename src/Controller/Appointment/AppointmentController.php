<?php

namespace App\Controller\Appointment;

use App\Controller\Traits\ErrorFormTrait;
use App\Entity\User;
use App\Entity\Appointment;
use App\Entity\AppointmentType;
use App\Enum\AppointmentStatus;
use App\Form\AppointmentFormType;
use App\Repository\AppointmentRepository;
use App\Service\AppointmentValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/rendezvous')]
final class AppointmentController extends AbstractController
{
    use ErrorFormTrait;

    #[Route('/type/{id}/form', name: 'app_appointment_ajax_form', methods: ['GET', 'POST'])]
    public function ajaxForm(
        AppointmentType $type,
        Request $request,
        EntityManagerInterface $em,
        AppointmentValidator $appointmentValidator,
        AppointmentRepository $appointmentRepo,
    ): Response {
        $appointment = new Appointment();
        $appointment->setType($type);

        $form = $this->createForm(AppointmentFormType::class, $appointment, [
            // options personnalisées au besoin
        ]);
        $form->handleRequest($request);

        /** @var User $user */
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation métier avant enregistrement
            $appointmentValidator->validate($appointment, $user, $form);

            // Si ajout erreurs, on ne persiste pas
            if ($form->getErrors(true)->count() > 0) {
                return $this->renderAppointmentForm($form, $type);
            }

            // Création du RDV
            $appointment->setUser($user);

            // Sécurité sur les dates
            $startAtParis = $appointment->getStartAt();  // objet en Europe/Paris grâce au form
            if (!$startAtParis) {
                return $this->renderAppointmentForm($form, $type, 'Merci de sélectionner une date de début.');
            }

            $duration = $appointment->getType()->getDuration();
            if ($duration < 0) {
                return $this->renderAppointmentForm($form, $type, 'La durée du type de rendez-vous est invalide.');
            }
            $endAtParis = $startAtParis->modify("+{$duration} minutes");

            // 👉 Conversion d'INSTANT vers UTC (change juste la représentation, pas l'instant)
            $utc = new \DateTimeZone('UTC');
            $startAtUtc = $startAtParis->setTimezone($utc);
            $endAtUtc   = $endAtParis->setTimezone($utc);

            // Affecte en UTC AVANT les contrôles & flush
            $appointment->setStartAt($startAtUtc);
            $appointment->setEndAt($endAtUtc);

            // Double-check overlap en UTC (et le repo doit bloquer PENDING + CONFIRMED)
            if ($appointmentRepo->hasOverlap($startAtUtc, $endAtUtc)) {
                return $this->renderAppointmentForm($form, $type, "Ce créneau vient d'être pris. Merci d'en choisir un autre.");
            }

            // Statut initial : PENDING (en attente de paiement)
            $appointment->setStatus(AppointmentStatus::PENDING);
            $em->persist($appointment);
            $em->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->render('appointment/confirmation.html.twig', [
                    'ajax' => true,
                ]);
            }

            // Prévoir redirection vers Stripe ou page de confirmation
            // return $this->redirectToRoute('app_appointment_confirmation');
            return $this->render('appointment/confirmation.html.twig');
        }

        return $this->renderAppointmentForm($form, $type);
    }
}
