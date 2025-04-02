<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ErrorController extends AbstractController
{
    #[Route('/error/two-many-sessions', name: 'error_too_many_sessions')]
    public function twoManySessions(): Response
    {
        $this->addFlash('danger', 'Connexion refusée : Trop de sessions actives.');
        return $this->render('error/two_many_sessions.html.twig');
    }
}
