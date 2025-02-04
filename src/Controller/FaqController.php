<?php

namespace App\Controller;

use App\Repository\FaqContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FaqController extends AbstractController
{
    public function __construct(protected FaqContentRepository $faqContentRepository)
    {}

    #[Route('/questions-frequentes', name: 'app_faq')]
    public function index(): Response
    {
        $faqs = $this->faqContentRepository->findAll();

        return $this->render('faq/index.html.twig', [
            'faqs' => $faqs,
        ]);
    }
}
