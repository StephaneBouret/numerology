<?php

namespace App\Controller\Purchase;

use App\Entity\Purchase;
use App\Form\PurchaseFormType;
use App\Repository\ProgramRepository;
use App\Service\PurchaseNumberGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PurchaseConfirmationController extends AbstractController
{
    public function __construct(protected ProgramRepository $programRepository, protected EntityManagerInterface $em, protected PurchaseNumberGenerator $numberGenerator)
    {}

    #[Route('/purchase/confirm/{slug}', name: 'app_purchase_confirm')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page')]
    public function confirm($slug, Request $request): Response
    {
        $program = $this->programRepository->findOneBy([
            'slug' => $slug
        ]);
        
        if (!$program) {
            $this->addFlash('warning', 'Vous ne pouvez pas confirmer une commande sans programme');
            return $this->redirectToRoute('app_purchase_checkout');
        }

        $user = $this->getUser();

        $form = $this->createForm(PurchaseFormType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('warning', 'Vous devez valider le formulaire de confirmation');
            return $this->redirectToRoute('app_purchase_checkout');
        }

        /** @var Purchase */
        $purchase = $form->getData();
        $purchase->setUser($user)
                ->setProgram($program)
                ->setNumber($this->numberGenerator->generate());

        $this->em->persist($purchase);
        $this->em->flush();

        // $this->addFlash('success', 'La commande a bien été enregistrée');
        return $this->redirectToRoute('purchase_payment_form', [
            'id' => $purchase->getId()
        ]);
    }
}