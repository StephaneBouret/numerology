<?php

namespace App\Controller\Admin;

use App\Entity\Testimonial;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class TestimonialCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Testimonial::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setPageTitle('index', 'Témoignages :')
            ->setPageTitle('edit', fn(Testimonial $testimonial) => (string) $testimonial->getId())
            ->setPageTitle('detail', fn(Testimonial $testimonial) => (string) $testimonial->getId())
            ->setEntityLabelInSingular('un témoignage')
            ->setDefaultSort(['id' => 'DESC'])
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
            BooleanField::new('isApproved', 'Est approuvé'),
            TextareaField::new('content', 'Contenu du témoignage')->renderAsHtml(),
            DateTimeField::new('createdAt', 'Créé le :')->onlyOnIndex(),
            IntegerField::new('rating', 'note /5 :')->onlyOnIndex(),
            AssociationField::new('author', 'Utilisateur :')
                ->formatValue(fn ($value, $entity) => $entity->getAuthor()?->getFullname() ?? 'Utilisateur inconnu')
                ->onlyOnIndex(),
        ];
    }
}
