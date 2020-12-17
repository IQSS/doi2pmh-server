<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $useradmin = new User();
        $useradmin->setEmail("admin@doi.org");
        $useradmin->setPassword($this->passwordEncoder->encodePassword(
            $useradmin,
            'oaipmh'
        ));
        $useradmin->setRoles(['ROLE_ADMIN']);
        $manager->persist($useradmin);

        $user = new User();
        $user->setEmail("user@doi.org");
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'oaipmh'
        ));
        $manager->persist($user);
        $manager->flush();
    }
}
