<?php

namespace App\Command;

use App\Entity\Program;
use App\Entity\ProgramTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:migrate-program-translations',
    description: 'Traduction de l\'entité Program',
)]
class MigrateProgramTranslationsCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $programs = $this->em->getRepository(Program::class)->findAll();

        foreach ($programs as $program) {
            $fields = [
                'name' => $program->getName(),
                'description' => $program->getDescription(),
                'satisfiedTitle' => $program->getSatisfiedTitle(),
                'satisfiedContent' => $program->getSatisfiedContent(),
                'showTitle' => $program->getShowTitle(),
                'showContent' => $program->getShowContent(),
                'detailTitle' => $program->getDetailTitle(),
            ];

            foreach ($fields as $field => $value) {
                if ($value !== null) {
                    $translation = new ProgramTranslation('fr', $field, $value);
                    $translation->setObject($program);

                    $this->em->persist($translation);
                }
            }
        }

        $this->em->flush();
        $output->writeln('Traductions initialisées en français avec succès.');

        return Command::SUCCESS;
    }
}
