<?php

namespace App\Controller;

use App\Form\CertificateNameType;
use App\Repository\CertificateRepository;
use App\Repository\ProgramRepository;
use App\Service\CertificateService;
use App\Service\CompletionService;
use App\Service\ProgramDurationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CertificateController extends AbstractController
{
    public function __construct(protected CertificateRepository $certificateRepository, protected ProgramRepository $programRepository, protected EntityManagerInterface $em)
    {}

    #[Route('/certificate/create/{program_slug}', name: 'app_certificate_create')]
    public function index(string $program_slug, CertificateService $certificateService, CompletionService $completionService): Response
    {
        $user = $this->getUser();
        $program = $this->programRepository->findOneBy([
            'slug' => $program_slug
        ]);
        if (!$program) {
            throw $this->createNotFoundException("Le programme demandé n'existe pas");
        }
        $this->denyAccessUnlessGranted('PROGRAM_VIEW', $program, "Vous n'avez pas accès à ce programme");

        // Vérifier si un certificat existe déjà
        $existingCertificate = $this->certificateRepository->findOneBy([
            'user' => $user,
            'program' => $program,
        ]);

        if ($existingCertificate) {
            return $this->redirectToRoute('app_certificate_show', [
                'uuid' => $existingCertificate->getUuid(),
            ]);
        }

        // Vérifier si le programme est terminé par l'utilisateur
        if (!$completionService->isProgramCompleted($user, $program)) {
            $this->addFlash('danger', "Le programme n'est pas encore complété.");
            return $this->redirectToRoute('app_sections', [
                'slug' => $program_slug,
            ]);
        }

        // Générer le certificat
        $certificate = $certificateService->generateCertificate($user, $program);

        return $this->redirectToRoute('app_certificate_show', [
            'uuid' => $certificate->getUuid(),
        ]);
    }

    #[Route('/certificate/{uuid}', name: 'app_certificate_show')]
    public function show(string $uuid, ProgramDurationService $durationService): Response
    {
        $certificate = $this->certificateRepository->findOneBy([
            'uuid' => $uuid
        ]);

        if (!$certificate) {
            throw $this->createNotFoundException('Certificat introuvable.');
        }

        $program = $certificate->getProgram();

        $durationHours = $durationService->calculateDuration($program);

        return $this->render('certificate/show.html.twig', [
            'certificate' => $certificate,
            'user' => $certificate->getUser(),
            'program' => $program,
            'durationHours' => $durationHours,
        ]);
    }

    #[Route('/certificate/{uuid}/update-name', name: 'app_certificate_update_name', methods: ['POST'])]
    public function updateName(string $uuid, Request $request): JsonResponse
    {
        $certificate = $this->certificateRepository->findOneBy(['uuid' => $uuid]);

        if (!$certificate) {
            return new JsonResponse(['success' => false, 'message' => 'Certificat introuvable.'], 404);
        }

        $form = $this->createForm(CertificateNameType::class, $certificate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $firstname = ucfirst($form->get('firstname')->getData());
            $lastname = mb_strtoupper($form->get('lastname')->getData());
            $certificate->setFirstname($firstname)
                        ->setLastname($lastname);
            $this->em->flush();

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Formulaire invalide.',
            'errors' => (string) $form->getErrors(true, false)
        ], 400);
    }
}
