<?php

namespace App\Controller;

use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProgramController extends AbstractController
{
    #[Route('/program/{slug}', name: 'app_program')]
    public function index($slug, ProgramRepository $programRepository): Response
    {
        $program = $programRepository->findOneBy([
            'slug' => $slug
        ]);
        
        if (!$program) {
            throw $this->createNotFoundException('Le programme n\'existe pas');
        }

        return $this->render('program/index.html.twig', [
            'program' => $program,
        ]);
    }
}
