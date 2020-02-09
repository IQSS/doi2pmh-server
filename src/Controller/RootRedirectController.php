<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class RootRedirectController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        return $this->redirectToRoute('oaipmh');
    }

    /**
    * @Route("/doi2pmh/admin/")
    */
    public function redirectToLocale(Request $request)
    {
        $lang = $request->getPreferredLanguage(array('en', 'fr'));
        return $this->redirectToRoute('admin_index', array("_locale"=> $lang!=null?$lang:'en'));
    }
}
