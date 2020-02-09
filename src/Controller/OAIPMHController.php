<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/doi2pmh")
 */
class OAIPMHController extends AbstractController
{
    /**
     * @Route("/", name="oaipmh")
     */
    public function index()
    {
        return $this->render('oaipmh/index.html.twig', [
            'controller_name' => 'OAIPMHController',
        ]);
    }
}
