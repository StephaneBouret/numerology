<?php

namespace App\Controller;

use App\Entity\LaunchNotification;
use App\Form\LaunchNotificationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LaunchNotificationController extends AbstractController
{
    #[Route('/maintenance', name: 'app_launch_notification_form', methods: ['GET'])]
    public function showForm(): Response
    {
        $form = $this->createForm(LaunchNotificationFormType::class);

        return $this->render('launch_notification/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/api/launch-notification', name: 'app_launch_notification', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $notification = new LaunchNotification();
        $form = $this->createForm(LaunchNotificationFormType::class, $notification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($notification);
            $em->flush();

            return $this->json(['success' => true, 'message' => 'Merci, vous serez notifié !']);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->json([
            'success' => false,
            'errors' => $errors,
        ], 400);
    }
}
