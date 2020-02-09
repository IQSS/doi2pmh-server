<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RootRedirectController extends AbstractController
{
    /**
     * @Route("/", name="root_redirect")
     */
    public function index()
    {
        return $this->redirectToRoute('oaipmh');
    }
}
