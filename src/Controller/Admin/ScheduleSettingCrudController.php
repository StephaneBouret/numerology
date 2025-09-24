<?php

namespace App\Controller\Admin;

use App\Entity\ScheduleSetting;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ScheduleSettingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ScheduleSetting::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular("Paramètre d'horaire")
            ->setEntityLabelInPlural("Horaires & jours ouvrés")
            ->setPageTitle(Crud::PAGE_INDEX, 'Horaires & jours ouvrés')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouveau paramètre')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le paramère')
            ->setEntityPermission('ROLE_ADMIN')
            ->setSearchFields(['setting_key', 'value']);
    }

    public function configureActions(Actions $actions): Actions
    {
        // On laisse tout (INDEX, NEW, EDIT, DELETE)
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();

        // Champ clé
        $keyField = TextField::new('setting_key', 'Clé')
            ->setHelp(
                "Clé supportées :
                <code>morning_start</code> (HH:MM),
                <code>morning_end</code> (HH:MM),
                <code>afternoon_start</code> (HH:MM),
                <code>afternoon_end</code> (HH:MM),
                <code>open_days</code> (ex: 1,2,3,4,5)"
            )
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'ex: morning_start',
                ],
            ]);

        if ($pageName === Crud::PAGE_EDIT) {
            // On vérouille la clé en édition afin d'éviter les bourdes
            $keyField = $keyField->setDisabled();
        }

        yield $keyField;

        // Champ valeur
        yield TextField::new('value', 'Valeur')
            ->setHelp(
                "Pour heures : format <code>HH:MM</code> (ex: 09:00).
                Pour <code>open_days</code> : liste de 1 à 7 séparés par des virgules (ex: 1,2,3,4,5 ; Lundi = 1)."
            )
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'ex: 09:00 ou 1,2,3,4,5',
                    // pattern généraliste : HH:MM OU liste 1..7 séparés par virgules
                    'pattern' => '(^\d{2}:\d{2}$)|(^[1-7](,[1-7])*$)',
                    'title' => 'Heure au format HH:MM (ex : 09:00) ou jours open_days (ex : 1,2,3,4,5)',
                    'inputmode' => 'text',
                ],
            ]);
    }
}
