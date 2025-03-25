<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\InvitationRepository;
use App\Service\AvatarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class InvitationController extends AbstractController
{
    public function __construct(private readonly InvitationRepository $invitationRepository, private readonly Security $security, private readonly EntityManagerInterface $em, private readonly AvatarService $avatarService)
    {}

    #[Route('/invitation/{uuid}', name: 'app_invitation', requirements: ['uuid' => '[\w-]+'])]
    public function index($uuid, Request $request, UserPasswordHasherInterface $userPassword): Response
    {
        $invitation = $this->invitationRepository->findOneBy([
            'uuid' => $uuid
        ]);
        // Vérifie si l'invitation existe
        if (!$invitation) {
            throw new NotFoundHttpException('Invitation non trouvée.');
        }
        // Vérifie si l'invitation a déjà été utilisée
        if ($invitation->getUser()) {
           throw new AccessDeniedException('Cette invitation est déjà utilisée');
        }

        $user = new User;
        $user->setEmail($invitation->getEmail());

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPassword->hashPassword($user, $plainPassword));
            $user->setFirstname(ucfirst($form->get('firstname')->getData()))
                ->setLastname(mb_strtoupper($form->get('lastname')->getData()))
                ->setAvatar($this->avatarService->createAndAssignAvatar($user))
                ->setRoles(['ROLE_GUEST']);
            $invitation->setUser($user);

            $this->em->persist($user);
            $this->em->flush();

            return $this->security->login($user, 'security.authenticator.form_login.main', 'main');
        }
        return $this->render('invitation/index.html.twig', [
            'registrationForm' => $form,
            'invitation' => $invitation,
        ]);
    }
}
