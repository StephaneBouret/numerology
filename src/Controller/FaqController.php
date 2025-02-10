<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Form\SearchFormType;
use App\Repository\FaqContentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

final class FaqController extends AbstractController
{
    public function __construct(protected FaqContentRepository $faqContentRepository)
    {}

    #[Route('/questions-frequentes', name: 'app_faq')]
    public function index(Request $request): Response
    {
        $data = new SearchData;
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchFormType::class, $data);
        $form->handleRequest($request);

        $faqs = $this->faqContentRepository->findSearch($data);
        // $faqs = $this->faqContentRepository->findAll();
        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('faq/_faqs.html.twig', ['faqs' => $faqs]),
                'pagination' => $this->renderView('partials/_pagination.html.twig', ['faqs' => $faqs]),
            ]);
        }

        return $this->render('faq/index.html.twig', [
            'faqs' => $faqs,
            'form' => $form
        ]);
    }
}
