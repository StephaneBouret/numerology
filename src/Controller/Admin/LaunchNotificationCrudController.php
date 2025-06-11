<?php

namespace App\Controller\Admin;

use App\Entity\LaunchNotification;
use App\Repository\LaunchNotificationRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LaunchNotificationCrudController extends AbstractCrudController
{
    public function __construct(protected SendMailService $mailer, protected LaunchNotificationRepository $launchNotificationRepository)
    {}

    public static function getEntityFqcn(): string
    {
        return LaunchNotification::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setPageTitle('index', 'Notifications :')
            ->setDefaultSort(['id' => 'ASC'])
            ->setPaginatorPageSize(10);
    }

    public function configureActions(Actions $actions): Actions
    {
        $sendNotifications = Action::new('sendNotifications', 'Envoyer les notifications')
            ->linkToCrudAction('sendNotifications')
            ->displayIf(fn() => $this->launchNotificationRepository->count(['isSent' => false]) > 0)
            ->addCssClass('btn btn-info')
            ->createAsGlobalAction();
        
        $actions = parent::configureActions($actions);
        $actions->disable(Action::NEW)
                ->remove(Crud::PAGE_INDEX, Action::EDIT)
                ->add(Crud::PAGE_INDEX, $sendNotifications);
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            EmailField::new('email', 'Email :'),
            DateTimeField::new('createdAt', 'Envoyé le :')->setFormat('short'),
            BooleanField::new('isSent', 'Envoyé ?')
                ->renderAsSwitch(false),
        ];
    }

    public function sendNotifications(EntityManagerInterface $em, AdminUrlGenerator $adminUrlGenerator): RedirectResponse
    {
        // Sélectionner toutes les notifications non envoyées
        $notifications = $this->launchNotificationRepository->findBy(['isSent' => false]);

        foreach ($notifications as $notification) {
            // Envoi de l'email
            $this->mailer->sendMail(
                null,
                'Notion de l\'application L\'Univers des nombres',
                $notification->getEmail(),
                'Notification de la date de lancement',
                'notification',
                []
            );

            // Mise à jour de la BDD
            $notification->setIsSent(true);
            $em->persist($notification);
        }

        // Sauvegarde des changements
        $em->flush();

        // Redirection vers la page d'index avec un message flash
        $this->addFlash('success', 'Les notifications ont été envorées aves succès.');
        $url = $adminUrlGenerator->setController(self::class)->setAction('index')->generateUrl();

        return $this->redirect($url);
    }
}
