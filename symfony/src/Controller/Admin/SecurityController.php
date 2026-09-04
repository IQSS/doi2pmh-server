<?php

namespace App\Controller\Admin;

use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    public function __construct(private EntityManagerInterface $entityManager, private readonly AuthenticationUtils $authenticationUtils)
    {
    }

    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     * @noinspection PhpUnused
     */
    #[Route(path: '/login', name: 'security_login')]
    public function login(): Response
    {
        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();
        return $this->render('admin/account/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'isCasAuthentication' => Configuration::getConfigurationInstance($this->entityManager)->isCasAuthentication()
        ]);
    }

    /**
     * @return void
     * @noinspection PhpUnused
     */
    #[Route(path: '/logout', name: 'security_logout', schemes: ['https'])]
    public function logout():void
    {
        // Nothing to do!
    }
}
