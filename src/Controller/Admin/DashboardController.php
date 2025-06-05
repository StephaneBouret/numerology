<?php

namespace App\Controller\Admin;

use App\Entity\About;
use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Certificate;
use App\Entity\Comments;
use App\Entity\Company;
use App\Entity\Contact;
use App\Entity\Courses;
use App\Entity\FaqContent;
use App\Entity\Invitation;
use App\Entity\LaunchNotification;
use App\Entity\Navigation;
use App\Entity\User;
use App\Entity\Program;
use App\Entity\NewsLetter;
use App\Entity\Purchase;
use App\Entity\Question;
use App\Entity\Sections;
use App\Entity\Setting;
use App\Entity\Testimonial;
use App\Entity\UserDevice;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle("L'univers des nombres")
            ->setLocales(['fr']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Formateur', 'fa-regular fa-address-card', About::class);
        yield MenuItem::linkToCrud('Entreprise', 'fa-solid fa-building', Company::class);
        yield MenuItem::linkToCrud('Programmes', 'fas fa-list-check', Program::class);
        yield MenuItem::linkToCrud('Sections', 'fa-fw fas fa-section', Sections::class);
        yield MenuItem::linkToCrud('Cours', 'fas fa-book-open', Courses::class);
        yield MenuItem::subMenu('Quiz', 'fa-solid fa-question')->setSubItems([
            MenuItem::linkToCrud('Questions', 'fa-regular fa-circle-question', Question::class),
            MenuItem::linkToCrud('Réponses', 'fas fa-reply', Answer::class),
        ]);
        yield MenuItem::linkToCrud('Navigation', 'fa-solid fa-route', Navigation::class);
        yield MenuItem::linkToCrud('Commandes', 'fa-solid fa-cart-shopping', Purchase::class);
        yield MenuItem::subMenu('FAQs', 'fa-solid fa-circle-question')->setSubItems([
            MenuItem::linkToCrud('Catégories des faqs', 'fa-solid fa-list', Category::class),
            MenuItem::linkToCrud('Questions des faqs', 'fa-solid fa-comments', FaqContent::class),
        ]);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Newsletter', 'fa-solid fa-envelope-open-text', NewsLetter::class);
        yield MenuItem::linkToCrud('Contacts', 'fa-regular fa-envelope', Contact::class);
        yield MenuItem::linkToCrud('Commentaires', 'fa-solid fa-comment', Comments::class);
        yield MenuItem::linkToCrud('Témoignages', 'fa-solid fa-face-smile', Testimonial::class);
        yield MenuItem::linkToCrud('Invitations', 'fas fa-envelope', Invitation::class);
        yield MenuItem::linkToCrud('Appareils', 'fa-solid fa-desktop', UserDevice::class);
        yield MenuItem::linkToCrud('Certificats', 'fa-solid fa-certificate', Certificate::class);
        yield MenuItem::linkToCrud('Maintenance', 'fas fa-cogs', Setting::class);
        yield MenuItem::linkToCrud('Notif. Lancement', 'fas fa-rocket', LaunchNotification::class);
        yield MenuItem::linkToRoute('Retour au site', 'fas fa-home', 'home_index');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
