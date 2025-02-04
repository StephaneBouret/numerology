<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setPageTitle('index', 'Catégories des Faqs :')
            ->setPageTitle('new', 'Créer une catégorie')
            ->setPageTitle('edit', fn (Category $category) => (string) $category->getName())
            ->setEntityLabelInSingular('une catégorie')
            ->setDefaultSort(['name' => 'ASC'])
            ->setPaginatorPageSize(10);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name', 'Nom de la catégorie'),
            TextareaField::new('content', 'Description de la catégorie')->hideOnIndex(),
            TextField::new('imageFile', 'Fichier image :')
                ->setFormType(VichImageType::class)
                ->setTranslationParameters(['form.label.delete' => 'Supprimer l\'image'])
                ->hideOnIndex(),
            ImageField::new('imageName', 'Image')
                ->setBasePath('/images/categories')
                ->onlyOnIndex(),
        ];
    }
}
