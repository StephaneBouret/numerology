<?php

namespace App\Controller\Admin;

use App\Entity\Program;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class ProgramCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Program::class;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addAssetMapperEntry('admin_check_name');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setPageTitle('index', 'Programmes :')
            ->setPageTitle('new', 'Créer un programme')
            ->setPageTitle('edit', fn (Program $program) => (string) $program->getName())
            ->setPageTitle('detail', fn (Program $program) => (string) $program->getName())
            ->setEntityLabelInSingular('un programme')
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
            TextField::new('name', 'Nom du programme')
                ->setHelp('Entre 3 et 30 caractères')
                ->addCssClass('char-count'),
            TextareaField::new('description', 'Description du programme')
                ->setHelp('Deux lignes maximum')
                ->hideOnIndex(),
            MoneyField::new('price', 'Prix du programme')->setCurrency('EUR'),
            TextField::new('imageFile', 'Fichier image :')
                ->setFormType(VichImageType::class)
                ->setTranslationParameters(['form.label.delete' => 'Supprimer l\'image'])
                ->hideOnIndex(),
            ImageField::new('imageName', 'Image')
                ->setBasePath('/images/programs')
                ->onlyOnIndex(),
            FormField::addFieldset('Partie Premier bloc')->renderCollapsed(),
            TextField::new('satisfiedTitle', 'Titre partie "Premier bloc"')->hideOnIndex(),
            TextEditorField::new('satisfiedContent', 'Contenu partie "Premier bloc"')
                ->hideOnIndex()
                ->setHelp('Texte avec mise en forme HTML autorisée'),
            FormField::addFieldset('Partie Deuxième bloc')->renderCollapsed(),
            TextField::new('showTitle', 'Titre partie "Deuxième bloc"')->hideOnIndex(),
            TextEditorField::new('showContent', 'Contenu partie "Deuxième bloc"')
                ->hideOnIndex()
                ->setHelp('Texte avec mise en forme HTML autorisée'),
            FormField::addFieldset('Partie Troisième bloc')->renderCollapsed(),
            TextField::new('detailTitle', 'Titre général pour le troisième bloc')->hideOnIndex(),
            CollectionField::new('details', 'Blocs de détails')
                ->useEntryCrudForm()
                ->setEntryIsComplex(true)
                ->hideOnIndex()
                ->setHelp('Ajoute plusieurs paragraphes de contenu détaillé'),
        ];
    }
}
