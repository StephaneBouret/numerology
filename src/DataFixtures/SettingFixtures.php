<?php

namespace App\DataFixtures;

use App\Entity\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class SettingFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['settings'];
    }

    public function load(ObjectManager $manager): void
    {
        $maintenance = new Setting();
        $maintenance->setSettingKey('maintenance')
                    ->setValue(false);

        $manager->persist($maintenance);
        $manager->flush();
    }
}
