<?php

namespace App\Controller;

use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home_index')]
    public function index(ProgramRepository $programRepository): Response
    {
        $programs = $programRepository->findBy([], ['name' => 'ASC']);

        return $this->render('home/index.html.twig', [
            'programs' => $programs,
        ]);
    }
}
