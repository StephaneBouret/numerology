<?php

namespace App\DataFixtures;

use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\String\Slugger\SluggerInterface;

class CompanyFixtures extends Fixture
{
    public function __construct(protected SluggerInterface $sluggger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new \Faker\Provider\fr_FR\Company($faker));
        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        $companyRawPhoneNumber = $faker->mobileNumber();
        $companyPhoneNumberObject = $phoneNumberUtil->parse($companyRawPhoneNumber, 'FR');

        $company = new Company();
        $company->setName($faker->company())
            ->setSlug($this->sluggger->slug($company->getName())->lower())
            ->setAdress($faker->streetAddress())
            ->setPostalCode($faker->postcode())
            ->setCity($faker->city)
            ->setPhone($companyPhoneNumberObject)
            ->setEmail($faker->companyEmail())
            ->setSiren($faker->siret())
            ->setManager($faker->name())
            ->setUrl($faker->url())
            ->setType($faker->randomElement([Company::TYPE_ASSOCIATION, Company::TYPE_EURL, Company::TYPE_MICRO, Company::TYPE_SARL, Company::TYPE_SAS, Company::TYPE_SASU]));

        $manager->persist($company);

        $manager->flush();
    }
}
