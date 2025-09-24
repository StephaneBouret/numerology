<?php

namespace App\Controller\Admin;

use App\Entity\Unavailability;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class UnavailabilityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Unavailability::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Indisponibilité horaire')
            ->setEntityLabelInPlural('Indisponibilités horaires')
            ->setPageTitle(Crud::PAGE_INDEX, 'Indisponibilités (plages horaires)')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle indisponibilité')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l\'indisponibilité')
            ->setEntityPermission('ROLE_ADMIN')
            ->setSearchFields(['reason'])
            ->setDefaultSort(['startAt' => 'ASC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(DateTimeFilter::new('startAt', 'Début'))
            ->add(DateTimeFilter::new('endAt', 'Fin'))
            ->add(TextFilter::new('reason', 'Motif'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            DateTimeField::new('startAt', 'Début')->setHelp('Date & Heure de début'),
            DateTimeField::new('endAt', 'Fin')->setHelp('Date & Heure de fin'),
            BooleanField::new('allDay', 'Journée entière')
                ->setHelp('Si coché, la plage sera normalisée à 00.00-23:59 pour le jour concerné'),
            TextField::new('reason', 'Motif (optionnel)')->hideOnIndex(),
        ];
    }

    // Normalise si "Journée entière" est cochée : 00:00-23:59 sur la date de startAt
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof Unavailability && $entityInstance->isAllDay()) {
            $d = $entityInstance->getStartAt();
            $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $d->format('Y-m-d') . ' 00:00:00');
            $end   = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $d->format('Y-m-d') . ' 23:59:59');
            $entityInstance->setStartAt($start);
            $entityInstance->setEndAt($end);
        }
        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof Unavailability && $entityInstance->isAllDay()) {
            $d = $entityInstance->getStartAt();
            $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $d->format('Y-m-d') . ' 00:00:00');
            $end   = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $d->format('Y-m-d') . ' 23:59:59');
            $entityInstance->setStartAt($start);
            $entityInstance->setEndAt($end);
        }
        parent::updateEntity($em, $entityInstance);
    }
}
