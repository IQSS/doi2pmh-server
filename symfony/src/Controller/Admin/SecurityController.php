<?php

namespace App\Controller\Admin;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("", name="security_")
 */
class SecurityController extends AbstractController
{

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     * @noinspection PhpUnused
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/account/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'isCasAuthentication' => Configuration::getConfigurationInstance($this->entityManager)->isCasAuthentication()
        ]);
    }

    /**
     * @Route("/logout", name="logout", schemes={"https"})
     * @return void
     * @noinspection PhpUnused
     */
    public function logout():void
    {
        // Nothing to do!
    }
}
