<?php

namespace App\Services\Oai\Verbs;

use App\Services\Oai\OaiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Represent the oai verbs
 * Interface OaiVerbInterface
 * @package App\Services\Oai\Verbs
 */
interface OaiVerbInterface
{
    /**
     * Set the request
     * @param Request $request
     * @return mixed
     */
    public function setRequest(Request $request);

    /**
     * @return Request
     */
    public function getRequest(): Request;

    /**
     * Set oai service
     * @param OaiService $oaiService
     * @return mixed
     */
    public function setOaiService(OaiService $oaiService);

    /**
     * @return OaiService
     */
    public function getOaiService(): OaiService;

    /**
     * Set the entity manager
     * @param EntityManagerInterface $entityManager
     * @return mixed
     */
    public function setEntityManager(EntityManagerInterface $entityManager);

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface;

    /**
     * Set allowed arguments for verb
     * @return mixed
     */
    public function setArguments();

    /**
     * @return array
     */
    public function getArguments(): array;

    /**
     * Generate and return the xml response
     * @return Response
     */
    public function getXmlResponse(): Response;
}
