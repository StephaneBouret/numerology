<?php

namespace App\Controller\Admin;

use App\Entity\Navigation;
use App\Repository\CoursesRepository;
use App\Repository\NavigationRepository;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NavigationCrudController extends AbstractCrudController
{
    public function __construct(protected NavigationRepository $navigationRepository, protected CoursesRepository $coursesRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Navigation::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $coursesNames = $this->coursesRepository->findAllNames();
        $navigationNames = $this->navigationRepository->findAllNames();

        // Extraire le dernier segment de chaque URL de navigation
        $navigationSlugs = array_map(fn ($name) => basename($name), $navigationNames);

        // Vérifier la correspondance entre les noms des cours et les slugs extraits de la navigation
        $namesMatch = empty(array_diff($coursesNames, $navigationSlugs));

        $createNavigation = Action::new('createNavigation', 'Créer la navigation')
            ->linkToCrudAction('createNavigation')
            ->addCssClass('btn btn-info')
            ->displayIf(fn() => $this->navigationRepository->count([]) === 0 || ($this->navigationRepository->count([]) < $this->coursesRepository->count([]) || !$namesMatch))
            ->createAsGlobalAction();

        $actions = parent::configureActions($actions);
        $actions->disable(Action::EDIT)
            ->disable(Action::DETAIL)
            ->disable(Action::NEW)
            ->disable(Action::DELETE)
            ->add(Crud::PAGE_INDEX, $createNavigation);
        return $actions;
    }

    #[Route('/admin/navigation/create', name: 'navigation_create')]
    public function createNavigation(ProgramRepository $programRepository, EntityManagerInterface $em, AdminUrlGenerator $adminUrlGenerator): Response
    {
        $program = $programRepository->find(1);
        $staticPath = "/courses/" . $program->getSlug();

        // Supprimer la table avant l'ajout de nouvelles entrées
        $this->navigationRepository->truncateTable();

        foreach ($this->coursesRepository->findAll() as $courses) {
            $navigation = new Navigation();
            $navigation->setPath($staticPath . "/" . $courses->getSection()->getSlug() . "/" . $courses->getSlug())
                ->setCourse($courses);
            $em->persist($navigation);
        }

        $em->flush();
        $this->addFlash('success', 'La navigation a bien été modifiée');

        // Redirection vers la page d'index du NavigationCrudController
        $url = $adminUrlGenerator->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
                    
        return $this->redirect($url);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('path', 'Chemin'),
        ];
    }
}
