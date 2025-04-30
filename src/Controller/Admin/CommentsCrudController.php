<?php

namespace App\Controller\Admin;

use App\Entity\Comments;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class CommentsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comments::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setPageTitle('index', 'Commentaires :')
            ->setPageTitle('edit', fn(Comments $comment) => (string) $comment->getId())
            ->setPageTitle('detail', fn(Comments $comment) => (string) $comment->getId())
            ->setEntityLabelInSingular('un commentaire')
            ->setDefaultSort(['id' => 'ASC'])
            ->setPaginatorPageSize(10);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        return $actions->remove(Crud::PAGE_INDEX, Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextareaField::new('content', 'Contenu du commentaire')->renderAsHtml(),
            DateTimeField::new('createdAt', 'Créé le :')->onlyOnIndex(),
            AssociationField::new('user', 'Utilisateur :')
                ->formatValue(fn ($value, $entity) => $entity->getUser()?->getFullname() ?? 'Utilisateur inconnu')
                ->onlyOnIndex(),
        ];
    }
}
