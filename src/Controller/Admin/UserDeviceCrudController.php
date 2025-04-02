<?php

namespace App\Controller\Admin;

use App\Entity\UserDevice;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserDeviceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserDevice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setPageTitle('index', 'Appareils :')
            ->setEntityLabelInSingular('un appareil')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        return $actions->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('deviceFingerprint')->hideOnIndex(),
            TextField::new('deviceType', 'Type :'),
            DateTimeField::new('createdAt', 'Créé le :'),
            TextField::new('browser', 'Navigateur :'),
            TextField::new('platform', 'OS :'),
            DateTimeField::new('lastUsedAt', 'Dernière connexion :'),
            AssociationField::new('user', 'Utilisateur :')
            ->formatValue(fn ($value, $entity) => $entity->getUser()?->getFullname() ?? 'Utilisateur inconnu'),
        ];
    }
}
