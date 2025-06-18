<?php

namespace App\Command;

use App\Entity\About;
use App\Entity\AboutTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:migrate-about-translations',
    description: 'Traduction de l\'entité About',
)]
class MigrateAboutTranslationsCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $abouts = $this->em->getRepository(About::class)->findAll();

        foreach ($abouts as $about) {
            $fields = [
                'description' => $about->getDescription(),
            ];

            foreach ($fields as $field => $value) {
                if ($value !== null) {
                    $translation = new AboutTranslation('fr', $field, $value);
                    $translation->setObject($about);

                    $this->em->persist($translation);
                }
            }
        }

        $this->em->flush();
        $output->writeln('Traductions initialisées en français avec succès.');
        return Command::SUCCESS;
    }
}
