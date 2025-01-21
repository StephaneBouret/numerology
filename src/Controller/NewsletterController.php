<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\NewsLetter;
use App\Form\NewsLetterFormType;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class NewsletterController extends AbstractController
{
    #[Route('/newsletter', name: 'app_newsletter')]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas le droit d\'accéder à cette page')]
    public function index(Request $request, EntityManagerInterface $em, SendMailService $mail): Response
    {
        /** @var User */
        $user = $this->getUser();
        $email = $user->getEmail();

        $new = new NewsLetter;
        $new->setEmail($user->getEmail());

        $form = $this->createForm(NewsLetterFormType::class, $new);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $new->setEmail($form->get('email')->getData())
                ->setFirstname($user->getFirstname())
                ->setLastname($user->getLastname());

            $em->persist($new);
            $em->flush();

            $mail->sendMail(
                null,
                'Inscription à la newsletter',
                'contact@numerology.fr',
                'Inscription à la newsletter de numérologie',
                'newsletter',
                ['user' => $user]
            );

            $this->addFlash('success', 'Votre demande a bien été envoyée');
            return $this->redirectToRoute('home_index');
        }

        return $this->render('newsletter/index.html.twig', [
            'form' => $form,
        ]);
    }
}
