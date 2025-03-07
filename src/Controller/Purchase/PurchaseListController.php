<?php

namespace App\Controller\Purchase;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PurchaseListController extends AbstractController
{
    #[Route('/purchases', name: 'app_purchase_list')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page')]
    public function index(): Response
    {
        /** @var User */
        $user = $this->getUser();
        return $this->render('purchase/list.html.twig', [
            'purchases' => $user->getPurchases(),
        ]);
    }
}
