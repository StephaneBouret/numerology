<?php

namespace App\Controller\Purchase;

use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use App\Stripe\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PurchasePaymentController extends AbstractController
{
    public function __construct(protected PurchaseRepository $purchaseRepository, protected StripeService $stripeService)
    {}

    #[IsGranted('ROLE_USER')]
    #[Route('/purchase/pay/{id}', name: 'purchase_payment_form')]
    public function showCardForm($id)
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_GUEST')) {
            $this->addFlash('warning', 'Vous n\'êtes pas autorisé à accéder à cette page');
            return $this->redirectToRoute('home_index');
        }
        
        $purchase = $this->purchaseRepository->find($id);

        if (
            !$purchase || 
            ($purchase && $purchase->getUser() !== $user) || 
            ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID)
        ) {
            return $this->redirectToRoute('app_purchase_list');
        }

        $paymentIntent = $this->stripeService->getPaymentIntent($purchase);

        return $this->render('purchase/payment.html.twig', [
            'clientSecret' => $paymentIntent->client_secret,
            'purchase' => $purchase,
            'stripePublicKey' => $this->stripeService->getPublicKey()
        ]);
    }
}