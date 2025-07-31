<?php

namespace App\Controller\Appointment;

use App\Entity\User;
use App\Entity\Appointment;
use App\Entity\AppointmentType;
use App\Form\AppointmentFormType;
use App\Repository\AppointmentRepository;
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
    #[Route('/type/{id}/form', name: 'app_appointment_ajax_form', methods: ['GET', 'POST'])]
    public function ajaxForm(
        AppointmentType $type,
        Request $request,
        EntityManagerInterface $em,
        AppointmentRepository $appointmentRepository
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
            $birthdate = $appointment->getEvaluatedPerson()->getBirthdate();
            $age = $birthdate ? $birthdate->diff(new \DateTimeImmutable())->y : null;

            // Vérification âge mini
            if ($type->getMinAge() !== null && $age < $type->getMinAge()) {
                $form->get('evaluatedPerson')->get('birthdate')->addError(new \Symfony\Component\Form\FormError("L'âge minimum pour ce rendez-vous est de {$type->getMinAge()} ans."));
            }

            // Vérification âge maxi
            if ($type->getMaxAge() !== null && $age > $type->getMaxAge()) {
                $form->get('evaluatedPerson')->get('birthdate')->addError(new \Symfony\Component\Form\FormError("L'âge maximum pour ce rendez-vous est de {$type->getMaxAge()} ans."));
            }

            // Vérification prérequis
            if ($type->getPrerequisite()) {
                $hasPrerequisite = $appointmentRepository->hasUserCompletedType($user, $type->getPrerequisite());
                if (!$hasPrerequisite) {
                    $form->addError(new \Symfony\Component\Form\FormError("Pour réserver ce rendez-vous, vous devez d'abord effectuer : " . $type->getPrerequisite()->getName()));
                }
            }

            // Si ajout erreurs, on ne persiste pas
            if (count($form->getErrors(true)) > 0) {
                return $this->render('appointment/_form.html.twig', [
                    'form' => $form,
                    'type' => $type,
                    'action' => $this->generateUrl('app_appointment_ajax_form', ['id' => $type->getId()]),
                ]);
            }

            // Création du RDV
            $appointment->setUser($user);
            // Calcul automatique de de la date de fin
            $startAt = $appointment->getStartAt();
            $duration = $appointment->getType()->getDuration(); // durée en minutes
            $endAt = (clone $startAt)->modify("+{$duration} minutes");
            $appointment->setEndAt($endAt);
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

        return $this->render('appointment/_form.html.twig', [
            'form' => $form,
            'type' => $type,
            'action' => $this->generateUrl('app_appointment_ajax_form', ['id' => $type->getId()]),
        ]);
    }
}
