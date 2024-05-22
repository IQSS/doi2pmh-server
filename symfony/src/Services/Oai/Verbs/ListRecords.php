<?php

namespace App\Services\Oai\Verbs;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\Doi;
use App\Services\Oai\Arguments\From;
use App\Services\Oai\Arguments\MetadataPrefix;
use App\Services\Oai\Arguments\ResumptionToken;
use App\Services\Oai\Arguments\Set;
use App\Services\Oai\Arguments\Until;
use App\Services\Oai\Arguments\Verb;

/**
 * Represent the ListRecords oai verb
 * ListRecords return all dois with there data
 * Class ListRecords
 * @package App\Services\Oai\Verbs
 */
class ListRecords implements OaiVerbInterface
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
        $listRecords = $this->oaiService->createElement('ListRecords');

        /**
         * @var Doi[]
         */
        $dois = $this->getDois();

        /** @var Doi $doi */
        foreach ($dois as $doi) {
            $record = $this->oaiService->createElement('record');
            $record->appendChild($this->getHeader($doi));
            if (!$this->isDeleted($doi)) {
                $metadata = $this->oaiService->createElement('metadata');
                $metadata->appendChild($this->getOaiDCContent($doi));
                $record->appendChild($metadata);
            }
            $listRecords->appendChild($record);
        }

        $this->updateResumptionToken($this->entityManager->getRepository(Doi::class)->count(['deleted' => false]));
        // Generate the resumption token content if is necessary
        if ($this->arguments['resumptionToken']->shouldBeIncluded()) {
            $listRecords->appendChild($this->arguments['resumptionToken']->generateToken());
        }

        return $this->oaiService->formatResponse($this->request, $listRecords);
    }
}
