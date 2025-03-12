<?php

namespace App\Controller;

use App\Repository\PurchaseRepository;
use App\Service\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class InvoiceController extends AbstractController
{
    public function __construct(protected PurchaseRepository $purchaseRepository, protected PdfGeneratorService $pdfGenerator)
    {
    }

    #[Route('/invoice/print/{id}', name: 'app_invoice_customer')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page')]
    public function index($id): Response
    {
        $purchase = $this->purchaseRepository->find($id);

        if (!$purchase) {
            throw $this->createNotFoundException("La commande demandée n'existe pas");
        }

        if ($purchase->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("La commande demandée n'existe pas");
        }

        $html = $this->renderView('invoice/index.html.twig', [
            'purchase' => $purchase
        ]);

        $filename = 'facture-' . $purchase->getNumber() . '.pdf';

        return $this->pdfGenerator->getStreamResponse($html, $filename);
    }

    #[Route('/admin/invoice/print/{id}', name: 'app_invoice_admin')]
    public function printForAdmin($id): Response
    {
        $purchase = $this->purchaseRepository->find($id);

        if (!$purchase) {
            return $this->redirectToRoute('admin');
        }

        $html = $this->renderView('invoice/index.html.twig', [
            'purchase' => $purchase
        ]);

        $filename = 'facture-' . $purchase->getNumber() . '.pdf';

        return $this->pdfGenerator->getStreamResponse($html, $filename);
    }

}
