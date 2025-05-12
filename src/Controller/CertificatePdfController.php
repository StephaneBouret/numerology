<?php

namespace App\Controller;

use App\Repository\CertificateRepository;
use App\Service\PdfGeneratorService;
use App\Service\ProgramDurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CertificatePdfController extends AbstractController
{
    public function __construct(protected CertificateRepository $certificateRepository, protected KernelInterface $kernel)
    {}

    #[Route('/certificate/print/{uuid}', name: 'app_certificate_print')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page')]
    public function index(string $uuid, PdfGeneratorService $pdfGenerator, ProgramDurationService $programDuration): Response
    {
        $certificate = $this->certificateRepository->findOneBy(['uuid' => $uuid]);

        if (!$certificate) {
            throw $this->createNotFoundException('Certificat introuvable');
        }

        $program = $certificate->getProgram();
        $durationHours = $programDuration->calculateDuration($program);
        $logo = $this->getBase64Image('static/logo.png');

        $html = $this-> renderView('certificate/create.html.twig', [
            'certificate' => $certificate,
            'user' => $certificate->getUser(),
            'program' => $program,
            'durationHours' => $durationHours,
            'logo' => $logo
        ]);

        $filename = 'certificat-' . $certificate->getUuid() . '.pdf';

        return $pdfGenerator->getLandscapeStreamResponse($html, $filename);
    }

    private function getBase64Image(string $relativePath): string
    {
        // Nettoie et construit le chemin absolu
        $absolutePath = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');

        // Normalise le chemin
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);

        // Vérifie l'existence
        if (!file_exists($normalizedPath)) {
            throw new \RuntimeException("Fichier image introuvable à l'emplacement : $normalizedPath");
        }

        // Encodage Base64
        $imageData = base64_encode(file_get_contents($normalizedPath));
        $mimeType = mime_content_type($normalizedPath);

        return 'data:' . $mimeType . ';base64,' . $imageData; 
    }
}
