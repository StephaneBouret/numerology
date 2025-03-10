<?php

namespace App\Controller\Purchase;

use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PurchasePaymentSuccessController extends AbstractController
{
    public function __construct(protected EntityManagerInterface $em, protected PurchaseRepository $purchaseRepository)
    {}

    #[IsGranted('ROLE_USER')]
    #[Route('/purchase/terminate/{id}', name: 'app_payment_success')]
    public function success($id): Response
    {
        $user = $this->getUser();
        $purchase = $this->purchaseRepository->find($id);
        if (
            !$purchase || 
            ($purchase && $purchase->getUser() !== $user) || 
            ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID)
        ) {
            $this->addFlash("warning", "La commande n'existe pas !");
            return $this->redirectToRoute('app_purchase_list');
        }

        $purchase->setStatus(Purchase::STATUS_PAID);
        $this->em->flush();

        $this->addFlash('success', 'La commande a été payée et confirmée !');
        return $this->redirectToRoute('app_purchase_list');
    }
}