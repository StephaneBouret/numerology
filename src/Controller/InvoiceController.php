<?php

namespace App\Controller;

use App\Repository\CompanyRepository;
use App\Repository\PurchaseRepository;
use App\Service\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class InvoiceController extends AbstractController
{
    public function __construct(protected PurchaseRepository $purchaseRepository, protected PdfGeneratorService $pdfGenerator, protected CompanyRepository $companyRepository) {}

    #[Route('/invoice/print/{id}', name: 'app_invoice_customer')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page')]
    public function index($id): Response
    {
        $purchase = $this->purchaseRepository->find($id);

        if (!$purchase || $purchase->getUser() != $this->getUser()) {
            throw $this->createNotFoundException("La commande demandée n'existe pas");
        }

        $company = $this->companyRepository->findOneBy([]);
        $tz = 'Europe/Paris';

        $html = $this->renderView('invoice/index.html.twig', [
            'purchase' => $purchase,
            'company' => $company,
            'tz' => $tz,
        ]);

        $ref = $purchase->getNumber() ?: (string) $purchase->getId();
        $filename = sprintf('facture-%s.pdf', $ref);

        return $this->pdfGenerator->getStreamResponse($html, $filename);
    }

    #[Route('/admin/invoice/print/{id}', name: 'app_invoice_admin')]
    public function printForAdmin($id): Response
    {
        $purchase = $this->purchaseRepository->find($id);

        if (!$purchase) {
            return $this->redirectToRoute('admin');
        }

        $company = $this->companyRepository->findOneBy([]);
        $tz = 'Europe/Paris';

        $html = $this->renderView('invoice/index.html.twig', [
            'purchase' => $purchase,
            'company' => $company,
            'tz' => $tz,
        ]);

        $ref = $purchase->getNumber() ?: (string) $purchase->getId();
        $filename = sprintf('facture-%s.pdf', $ref);

        return $this->pdfGenerator->getStreamResponse($html, $filename);
    }
}
