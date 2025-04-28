<?php

namespace App\Controller\Admin;

use App\Entity\ProgramDetail;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

class ProgramDetailCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProgramDetail::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextEditorField::new('content', 'Contenu du bloc')
                ->setHelp('Peut contenir du texte formaté en HTML'),
        ];
    }
}
