<?php

namespace App\Command;

use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:init-settings',
    description: 'Initialise la table maintenance en BDD',
)]
class InitSettingsCommand extends Command
{
    public function __construct(protected EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $setting = new Setting();
        $setting->setSettingKey('maintenance')
            ->setValue(false);

        $this->em->persist($setting);
        $this->em->flush();

        $output->writeln('<info>Paramètre "maintenance" initialisé</info>');
        return Command::SUCCESS;
    }
}
