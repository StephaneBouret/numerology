<?php

namespace App\Controller\Admin;

use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AnswerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Answer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Réponse')
            ->setEntityLabelInPlural('Réponses')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des réponses')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la réponse')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer une réponse');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('content', 'Contenu de la réponse')
                ->setRequired(true)
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Tapez la réponse',
                    ],
                ]),
            BooleanField::new('isCorrect', 'Bonne réponse')
                ->setHelp('Cochez cette case si la réponse est correcte')
                ->renderAsSwitch(true),
            AssociationField::new('question', 'Question associée')
                ->setRequired(true)
                ->setQueryBuilder(
                    fn(QueryBuilder $queryBuilder) => $queryBuilder->getEntityManager()->getRepository(Question::class)
                        ->createQueryBuilder('q')
                        ->orderBy('q.title', 'ASC')
                )
                ->autocomplete(),
        ];
    }
}
