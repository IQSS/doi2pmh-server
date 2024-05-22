<?php

namespace App\Services\Oai\Verbs;

use App\Entity\Configuration;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Services\Oai\Arguments\Verb;

/**
 * Represent the Identify oai verb
 * Identify return informations about the oai repository
 * Class Identify
 * @package App\Services\Oai\Verbs
 */
class Identify implements OaiVerbInterface
{
    use OaiVerbTrait;

    /**
     * @see OaiVerbInterface
     */
    public function setArguments()
    {
        $this->arguments = ['verb' => new Verb($this)];
    }

    /**
     * @see OaiVerbInterface
     * @return Response
     */
    public function getXmlResponse(): Response
    {
        $configuration = Configuration::getConfigurationInstance($this->getEntityManager());

        $identify = $this->oaiService->createElement('Identify');
        $identify->appendChild($this->oaiService->createElement('repositoryName', $configuration->getRepositoryName()));
        $identify->appendChild($this->oaiService->createElement('baseURL', $this->oaiService->router->generate('oai_index',  [], UrlGeneratorInterface::ABSOLUTE_URL)));
        $identify->appendChild($this->oaiService->createElement('adminEmail', $configuration->getAdminEmail()));
        $identify->appendChild($this->oaiService->createElement('earliestDatestamp', $configuration->getEarliestDatestamp()->format('Y-m-d')));
        $identify->appendChild($this->oaiService->createElement('deletedRecord', 'persistent'));
        $identify->appendChild($this->oaiService->createElement('granularity', 'YYYY-MM-DD'));

        return $this->oaiService->formatResponse($this->request, $identify);
    }
}
