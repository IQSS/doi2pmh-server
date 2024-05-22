<?php

namespace App\DataFixtures;

use App\Entity\Configuration;
use App\Entity\Folder;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Component\Validator\Constraints\Date;

class BootstrapFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordEncoder)
    {
    }

    public function load(ObjectManager $manager)
    {
        // Create a user admin
        $useradmin = new User();
        $useradmin->setEmail("admin@example.com");
        $useradmin->setPassword($this->passwordEncoder->hashPassword(
            $useradmin,
            'oaipmh'
        ));
        $useradmin->setIsAdmin(true);
        $manager->persist($useradmin);
        $manager->flush();

        // Create the root folder
        $rootFolder = new Folder();
        $rootFolder->setName('Doi2Pmh');
        $useradmin->setRootFolder($rootFolder);
        $manager->persist($rootFolder);
        $manager->persist($useradmin);
        $manager->flush();

        // Create a basic user
        $userlambda = new User();
        $userlambda->setEmail("user@example.com");
        $userlambda->setPassword($this->passwordEncoder->hashPassword(
            $userlambda,
            'oaipmh'
        ));
        $userlambda->setIsAdmin(false);
        $manager->persist($userlambda);
        $manager->flush();

        // Create a basic folder
        $lambdaFolder = new Folder();
        $lambdaFolder->setName('Lambda');
        $lambdaFolder->setParent($rootFolder);
        $userlambda->setRootFolder($lambdaFolder);
        $manager->persist($lambdaFolder);
        $manager->persist($userlambda);
        $manager->flush();

        //Add basic configuration
        $configuration = new Configuration();
        $configuration->setAdminEmail('admin@example.com');
        $configuration->setEarliestDatestamp(new DateTime('now'));
        $configuration->setUpdatedDoiLogs('Never updated');
        $manager->persist($configuration);
        $manager->flush();
    }
}
