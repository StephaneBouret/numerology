<?php

namespace App\Controller\Admin;

use App\Entity\Purchase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PurchaseCrudController extends AbstractCrudController
{
    public function __construct(protected AdminUrlGenerator $adminUrl)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Purchase::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setPageTitle('index', 'Commandes :')
            ->setPageTitle('detail', fn (Purchase $purchase) => (string) $purchase->getNumber())
            ->setEntityLabelInSingular('une commande')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10);
    }

    public function configureActions(Actions $actions): Actions
    {
        $generatePdf = Action::new('generatePdf', 'Générer PDF')
            ->linkToUrl(function (Purchase $purchase) {
                return $this->adminUrl
                    ->setRoute('app_invoice_admin', ['id' => $purchase->getId()])
                    ->generateUrl();
            })
            ->setIcon('fa fa-file-pdf')
            ->addCssClass('btn btn-secondary')
            ->displayIf(fn (Purchase $purchase) => $purchase->getStatus() === Purchase::STATUS_PAID);
        $actions = parent::configureActions($actions);
        $actions->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $generatePdf)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('number', 'Numéro de commande :'),
            AssociationField::new('user', 'Utilisateur :'),
            MoneyField::new('amount', 'Total :')->setCurrency('EUR'),
            DateTimeField::new('createdAt', 'Date de la commande :'),
            TextField::new('status', 'Statut :')
                ->formatValue(function ($value) {
                    if ($value === Purchase::STATUS_PAID) {
                        return '<span class="badge text-bg-success">Payée</span>';
                    }
                    if ($value === Purchase::STATUS_PENDING) {
                        return '<span class="badge text-bg-warning">En attente</span>';
                    }
                    return $value;
                })
            ->renderAsHtml(),
            AssociationField::new('program', 'Programme de formation :')
            ->onlyOnDetail()
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(BooleanFilter::new('status')
            ->setFormTypeOption('choices', [
                'En attente' => Purchase::STATUS_PENDING,
                'Payée' => Purchase::STATUS_PAID,
            ]));
    }
}
