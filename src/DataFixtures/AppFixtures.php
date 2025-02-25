<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(protected UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $phoneNumberUtil = PhoneNumberUtil::getInstance(); // Instance de PhoneNumberUtil

        $admin = new User();
        $hash = $this->passwordHasher->hashPassword($admin, "password");

        // Générer un numéro de téléphone pour l'admin
        $adminRawPhoneNumber = $faker->mobileNumber();
        $adminPhoneNumberObject = $phoneNumberUtil->parse($adminRawPhoneNumber, 'FR');

        $admin->setEmail("admin@gmail.com")
            ->setFirstname("Admin")
            ->setLastname("Admin")
            ->setRoles(['ROLE_ADMIN'])
            ->setAdress($faker->streetAddress())
            ->setPostalCode($faker->postcode())
            ->setCity($faker->city)
            // ->setPhone($faker->phoneNumber())
            ->setPhone($adminPhoneNumberObject)
            ->setPassword($hash);

        $manager->persist($admin);

        $users = [];
        for ($u = 0; $u < 5; $u++) {
            $user = new User();
            $hash = $this->passwordHasher->hashPassword($user, "password");

            // Générer un numéro de téléphone différent pour chaque utilisateur
            $rawPhoneNumber = $faker->mobileNumber();
            $phoneNumberObject = $phoneNumberUtil->parse($rawPhoneNumber, 'FR');

            $user->setEmail("user$u@gmail.com")
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setAdress($faker->streetAddress())
                ->setPostalCode($faker->postcode())
                ->setCity($faker->city)
                // ->setPhone($faker->phoneNumber())
                ->setPhone($phoneNumberObject)
                ->setPassword($hash);

            $manager->persist($user);
            $users[] = $user;
        }

        $manager->flush();
    }
}
