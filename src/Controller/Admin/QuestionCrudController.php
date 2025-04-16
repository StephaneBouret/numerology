<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Entity\Sections;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setEntityLabelInSingular('Question')
            ->setEntityLabelInPlural('Questions')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des Questions')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer une Question')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier une Question')
            ->setDefaultSort(['id' => 'ASC'])
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('title', 'Titre de la question')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Tapez la question ici',
                    ]])
                ->setRequired(true)
                ->setHelp('Le titre de la question est obligatoire !'),
            BooleanField::new('multiple', 'Question à choix multiples ?')
                ->renderAsSwitch(false)
                ->setHelp('Cochez cette case si la question a plusieurs réponses possibles'),
            TextEditorField::new('explanation', 'Explication de la réponse'),
            AssociationField::new('section', 'Section associée')
                ->setRequired(true)
                ->setHelp('Sélectionnez la section à laquelle appartient cette question')
                ->setQueryBuilder(
                    fn (QueryBuilder $queryBuilder) => $queryBuilder->getEntityManager()->getRepository(Sections::class)->createQueryBuilder('s')->orderBy('s.name', 'ASC')
                )
                ->autocomplete(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('title')
            ->add('section');
    }
}
