<?php

namespace App\Controller\Admin;

use App\Entity\Invitation;
use App\Repository\InvitationRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class InvitationCrudController extends AbstractCrudController
{
    public function __construct(protected SendMailService $mailer, protected InvitationRepository $invitationRepository)
    {}

    public static function getEntityFqcn(): string
    {
        return Invitation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setPageTitle('index', 'Invitations :')
            ->setPageTitle('edit', fn(Invitation $invitation) => (string) $invitation->getId())
            ->setPageTitle('detail', fn(Invitation $invitation) => (string) $invitation->getId())
            ->setEntityLabelInSingular('une invitation')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function configureActions(Actions $actions): Actions
    {
        $sendInvitations = Action::new('sendInvitations', 'Envoyer les invitations')
            ->linkToCrudAction('sendInvitations')
            ->displayIf(fn() => $this->invitationRepository->count(['isSent' => false]) > 0)
            ->addCssClass('btn btn-info')
            ->createAsGlobalAction();

        $actions = parent::configureActions($actions);
        $actions->disable(Action::EDIT)
            ->add(Crud::PAGE_INDEX, $sendInvitations)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email', 'Email :')
                ->setFormTypeOptions(['attr' => ['placeholder' => 'Email de l\'invité']]),
            TextField::new('uuid', 'Uuid :')
                ->hideWhenCreating(),
            AssociationField::new('user', 'Utilisateur :')
                ->hideWhenCreating(),
            BooleanField::new('isSent', 'Envoyé ? :')
                ->renderAsSwitch(false)
                ->hideWhenCreating(),
        ];
    }

    public function sendInvitations(
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator
    ): RedirectResponse {
        // Sélectionne toutes les invitations non envoyées
        $invitations = $entityManager->getRepository(Invitation::class)->findBy(['isSent' => false]);

        foreach ($invitations as $invitation) {
            // Envoie Email
            $this->mailer->sendMail(
                null,
                'Invitation de l\'application Univers des nombres',
                $invitation->getEmail(),
                'Invitation pour vous enregistrer sur l\'application Univers des nombres',
                'invitation',
                ['uuid' => $invitation->getUuid()]
            );

            // Mettre à jour l'invitation comme envoyée
            $invitation->setIsSent(true);
            $entityManager->persist($invitation);
        }

        // Sauvegarde les changements
        $entityManager->flush();

        // Redirige vers la page d'index avec un message flash
        $this->addFlash('success', 'Les invitations ont été envoyées avec succès !');
        $url = $adminUrlGenerator->setController(self::class)->setAction('index')->generateUrl();

        return $this->redirect($url);
    }
}
