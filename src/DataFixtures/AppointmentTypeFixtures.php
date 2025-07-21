<?php

namespace App\DataFixtures;

use App\Entity\AppointmentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AppointmentTypeFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['appointmentType'];
    }

    public function load(ObjectManager $manager): void
    {
        $types = [];

        $types['jeune_adulte'] = (new AppointmentType())
            ->setName('Jeune adulte')
            ->setDuration(60)
            ->setMinAge(18)
            ->setMaxAge(23)
            ->setPrice(7000)
            ->setParticipants(1)
            ->setIsPack(false);

        $types['enfant'] = (new AppointmentType())
            ->setName('Enfant')
            ->setDuration(60)
            ->setMinAge(0)
            ->setMaxAge(17)
            ->setPrice(7000)
            ->setParticipants(1)
            ->setIsPack(false);

        $types['adulte_identite'] = (new AppointmentType())
            ->setName('Analyse identité Adulte')
            ->setDuration(75)
            ->setMinAge(24)
            ->setMaxAge(null)
            ->setPrice(9000)
            ->setParticipants(1)
            ->setIsPack(false);

        $types['adulte_potentiels'] = (new AppointmentType())
            ->setName('Analyse des potentiels')
            ->setDuration(75)
            ->setMinAge(24)
            ->setMaxAge(null)
            ->setPrice(9000)
            ->setParticipants(1)
            ->setIsPack(false);

        $types['adulte_cycles'] = (new AppointmentType())
            ->setName('Analyse des cycles temporels')
            ->setDuration(75)
            ->setMinAge(24)
            ->setMaxAge(null)
            ->setPrice(9000)
            ->setParticipants(1)
            ->setIsPack(false);

        $types['couple'] = (new AppointmentType())
            ->setName('Analyse couple')
            ->setDuration(120)
            ->setMinAge(18)
            ->setMaxAge(null)
            ->setPrice(14000)
            ->setParticipants(2)
            ->setIsPack(false);

        $types['pack'] = (new AppointmentType())
            ->setName('Pack Analyse globale')
            ->setDuration(235) // 3*75min + transitions éventuelles
            ->setMinAge(24)
            ->setMaxAge(null)
            ->setPrice(25000)
            ->setParticipants(1)
            ->setIsPack(true);

        // Liaisons des prérequis
        $types['adulte_potentiels']->setPrerequisite($types['adulte_identite']);
        $types['adulte_cycles']->setPrerequisite($types['adulte_identite']);

        // Persistance
        foreach ($types as $type) {
            $manager->persist($type);
        }

        $manager->flush();
    }
}
