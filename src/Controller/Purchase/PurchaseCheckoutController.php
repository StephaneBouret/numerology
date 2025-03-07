<?php

namespace App\Controller\Purchase;

use App\Form\PurchaseFormType;
use App\Repository\ProgramRepository;
use App\Repository\PurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PurchaseCheckoutController extends AbstractController
{
    #[Route('/purchase/checkout/{slug}', name: 'app_purchase_checkout')]
    public function index($slug, ProgramRepository $programRepository, PurchaseRepository $purchaseRepository): Response
    {
        $user = $this->getUser();
        $program = $programRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$program) {
            throw $this->createNotFoundException('Le programme n\'existe pas');
        }

        // Vérifier si l'utilisateur a déjà acheté ce programme
        $purchaseExists = $purchaseRepository->findOneBy([
            'user' => $user,
            'program' => $program
        ]);

        if ($purchaseExists) {
            $this->addFlash('warning', 'Vous avez déjà acheté ce programme.');
            return $this->redirectToRoute('home_index');
        }

        $form = $this->createForm(PurchaseFormType::class);

        return $this->render('purchase/index.html.twig', [
            'user' => $user,
            'program' => $program,
            'confirmationForm' => $form
        ]);
    }
}
