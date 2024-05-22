<?php

namespace App\Services\Oai\Verbs;

use App\Entity\Doi;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Oai\Arguments\From;
use App\Services\Oai\Arguments\MetadataPrefix;
use App\Services\Oai\Arguments\ResumptionToken;
use App\Services\Oai\Arguments\Set;
use App\Services\Oai\Arguments\Until;
use App\Services\Oai\Arguments\Verb;

/**
 * Represent the ListIdentifiers oai verb
 * ListIdentifiers return only dois (not there data)
 * Class ListIdentifiers
 * @package App\Services\Oai\Verbs
 */
class ListIdentifiers implements OaiVerbInterface
{
    use OaiVerbTrait, ArgumentFilterTrait, ResumptionTokenTrait;

    /**
     * @see OaiVerbInterface
     */
    public function setArguments()
    {
        $this->arguments = [
            'verb' => new Verb($this),
            'from' => new From($this),
            'until' => new Until($this),
            'metadataPrefix' => new MetadataPrefix($this),
            'set' => new Set($this),
            'resumptionToken' => new ResumptionToken($this)
        ];
    }

    /**
     * @see OaiVerbInterface
     * @return Response
     */
    public function getXmlResponse(): Response
    {
        $listIdentifiers = $this->oaiService->createElement('ListIdentifiers');

        /**
         * @var Doi[]
         */
        $dois = $this->getDois();

        foreach ($dois as $doi) {
            $listIdentifiers->appendChild($this->getHeader($doi));
        }

        $this->updateResumptionToken($this->entityManager->getRepository(Doi::class)->count(['deleted' => false]));
        // Generate the resumption token content if is necessary
        if ($this->arguments['resumptionToken']->shouldBeIncluded()) {
            $listIdentifiers->appendChild($this->arguments['resumptionToken']->generateToken());
        }

        return $this->oaiService->formatResponse($this->request, $listIdentifiers);
    }
}
