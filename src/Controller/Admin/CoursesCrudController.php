<?php

namespace App\Controller\Admin;

use App\Entity\Courses;
use App\Entity\Program;
use App\Entity\Sections;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Vich\UploaderBundle\Form\Type\VichFileType;

class CoursesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Courses::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setPageTitle('index', 'Cours :')
            ->setPageTitle('new', 'Créer un cours')
            ->setPageTitle('edit', fn (Courses $courses) => (string) $courses->getName())
            ->setPageTitle('detail', fn (Courses $courses) => (string) $courses->getName())
            ->setEntityLabelInSingular('un cours')
            ->setDefaultSort(['id' => 'ASC'])
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name', 'Nom du cours'),
            TextareaField::new('shortDescription', 'Description courte')->hideOnIndex(),
            ChoiceField::new('contentType')
                ->setLabel('Type de contenu')
                ->setChoices([
                    'Lien' => Courses::TYPE_LINK,
                    'Quiz' => Courses::TYPE_QUIZ,
                    'Twig' => Courses::TYPE_TWIG,
                    'Video' => Courses::TYPE_VIDEO
                ])
                ->renderAsBadges()
                ->setRequired(true),
            AssociationField::new('program', 'Programme de formation')
                ->setQueryBuilder(
                    fn(QueryBuilder $queryBuilder) => $queryBuilder->getEntityManager()->getRepository(Program::class)->createQueryBuilder('p')->orderBy('p.name')
                )
                ->autocomplete(),
            AssociationField::new('section', 'Choisir une section')
                ->setQueryBuilder(
                    fn(QueryBuilder $queryBuilder) => $queryBuilder->getEntityManager()->getRepository(Sections::class)->createQueryBuilder('p')->orderBy('p.name')
                )
                ->autocomplete(),
            FormField::addFieldset('Fichiers :'),
            TextField::new('partialFile', 'Fichier :')
                ->setFormType(VichFileType::class)
                ->setFormTypeOption('download_label', function ($object) {
                    return $object->getPartialFileName();
                })
                ->setFormTypeOption('delete_label', 'Supprimer le fichier')
                ->setTranslationParameters(['form.label.delete' => 'Supprimer le fichier'])
                ->hideOnIndex(),
            TextField::new('partialFileName', 'Fichier')
                ->setHelp('Nom du fichier téléchargé : {{ this.partialFileName }}')
                ->onlyOnIndex(),
        ];
    }
}
