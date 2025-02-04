<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\FaqContent;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class FaqContentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FaqContent::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setPageTitle('index', 'Questions des Faqs :')
            ->setPageTitle('new', 'Créer une question')
            ->setPageTitle('edit', fn (FaqContent $faq) => (string) $faq->getName())
            ->setEntityLabelInSingular('une question')
            ->setDefaultSort(['id' => 'ASC'])
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
            TextField::new('name', 'Nom de la question'),
            TextEditorField::new('content', 'Description de la réponse')->hideOnIndex(),
            AssociationField::new('category', 'Catégorie associée')
                ->autocomplete(),
        ];
    }
}
