<?php

namespace App\Controller\Course;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SectionsController extends AbstractController
{
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas le droit d\'accéder à cette page')]
    #[Route('/courses/{slug}', name: 'app_sections', priority: -1)]
    public function index(): Response
    {
        return $this->render('sections/index.html.twig', [
            'controller_name' => 'SectionsController',
        ]);
    }
}
