<?php

namespace App\Service;

use App\Entity\Certificate;
use App\Entity\Program;
use App\Entity\User;
use App\Service\CompletionService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CertificateRepository;
use Symfony\Component\Uid\Uuid;

class CertificateService
{
    public function __construct(protected CompletionService $completionService, protected EntityManagerInterface $em, protected CertificateRepository $certificateRepository)
    {}

    public function generateCertificate(User $user, Program $program): Certificate
    {
        // Vérifier si le programme est complété
        if (!$this->completionService->isProgramCompleted($user, $program)) {
            throw new \Exception("Le programme n'est pas encore complété.");
        }

        // Vérifier si un certificat existe déjà
        $existingCertificate = $this->certificateRepository->findOneBy([
            'user' => $user,
            'program' => $program,
        ]);

        if ($existingCertificate) {
            return $existingCertificate; // Retourner le certificat existant
        }

        // Créer un nouveau certificat
        $certificate = new Certificate();
        $certificate->setUuid('UN-' . Uuid::v4())
            ->setUser($user)
            ->setProgram($program)
            ->setCreatedAt(new \DateTimeImmutable());

        // Enregistrer le certificat dans la base de données
        $this->em->persist($certificate);
        $this->em->flush();

        return $certificate;
    }
}