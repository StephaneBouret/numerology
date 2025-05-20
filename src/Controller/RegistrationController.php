<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Google\GoogleService;
use App\Security\EmailVerifier;
use App\Service\AvatarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier, protected RequestStack $requestStack)
    {
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, AvatarService $avatarService, GoogleService $googleService): Response
    {
        $session = $this->requestStack->getSession();
        $targetPath = $request->query->get('target', $session->get('_security.main.target_path'));
        if ($targetPath) {
            $session->set('_security.main.target_path', $targetPath);
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $firstname = $form->get('firstname')->getData();
            $lastname = $form->get('lastname')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Transform first and lastname
            $user->setFirstname(ucfirst($firstname))
                ->setLastname(mb_strtoupper($lastname));

            $user->setAvatar($avatarService->createAndAssignAvatar($user));

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            // $this->emailVerifier->sendEmailConfirmation(
            //     'app_verify_email',
            //     $user,
            //     (new TemplatedEmail())
            //         ->from(new Address('ftuncq@gmail.com', 'Potentiel Consulting'))
            //         ->to((string) $user->getEmail())
            //         ->subject('Merci de confirmer votre email')
            //         ->htmlTemplate('registration/confirmation_email.html.twig')
            // );

            // do anything else you need here, like send an email
            $redirectResponse = $security->login($user, 'security.authenticator.form_login.main', 'main');
            return $redirectResponse;
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'google_api_key' => $googleService->getGoogleKey(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre adresse email a bien été vérifiée !');

        return $this->redirectToRoute('home_index');
    }

    #[Route('/resend-verification', name: 'app_resend_verification_email')]
    public function resendVerificationEmail()
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user || $user->isVerified()) {
            $this->addFlash('info', 'Votre compte est déjà activé ou l\'utilisateur n\'existe pas.');

            return $this->redirectToRoute('app_home');
        }

        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('ftuncq@gmail.com', 'Potentiel Consulting'))
                ->to((string) $user->getEmail())
                ->subject('Merci de confirmer votre email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        $this->addFlash('success', 'Un nouveau lien d\'activation vous a été envoyé par email.');

        return $this->redirectToRoute('home_index');
    }
}
