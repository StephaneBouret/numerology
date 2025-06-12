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
use Symfony\Contracts\Translation\TranslatorInterface;

final class NewsletterController extends AbstractController
{
    #[Route('/newsletter', name: 'app_newsletter')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page')]
    public function index(Request $request, EntityManagerInterface $em, SendMailService $mail, TranslatorInterface $translator): Response
    {
        /** @var User */
        $user = $this->getUser();
        $email = $user->getEmail();
        // $this->denyAccessUnlessGranted('CAN_EDIT', $user, 'Vous n\'avez pas confimé votre email');

        $new = new NewsLetter;
        $new->setEmail($email);

        $form = $this->createForm(NewsLetterFormType::class, $new);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $inputEmail = $form->get('email')->getData();
            $new->setEmail($inputEmail)
                ->setFirstname($user->getFirstname())
                ->setLastname($user->getLastname());

            $em->persist($new);
            $em->flush();

            $mail->sendMail(
                null,
                'Inscription à la newsletter',
                'contact@luniversdesnombres.com',
                'Inscription à la newsletter de numérologie',
                'newsletter',
                [
                    'user' => $user,
                    'inputEmail' => $inputEmail,
                ]
            );

            $message = $translator->trans('Your request has been sent');
            $this->addFlash('success', $message);
            return $this->redirectToRoute('home_index');
        }

        return $this->render('newsletter/index.html.twig', [
            'form' => $form,
        ]);
    }
}
