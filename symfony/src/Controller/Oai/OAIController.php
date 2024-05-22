<?php

namespace App\Controller\Oai;

use App\Services\DoiService;
use App\Services\Oai\OaiService;
use App\Services\Oai\Verbs\OaiVerbInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OAIController
 * @package App\Controller\Oai
 * @Route("/oai", name="oai_")
 */
class OAIController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OaiService $oaiService,
        private DoiService $doiService
    )
    {
    }

    /**
     * @Route("/", methods={"GET"}, name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        /**
         * @var OaiVerbInterface
         */
        $verb = $this->oaiService->getVerb($request);

        $verb->setOaiService($this->oaiService);
        $verb->setEntityManager($this->entityManager);

        return $verb->getXmlResponse();
    }
    /**
     * @Route("/refresh", methods={"GET"}, name="refresh")
     * @return Response
     */
    public function refreshData(): Response
    {
        $count = $this->doiService->refreshDois();
        return new Response($count);
    }
}
