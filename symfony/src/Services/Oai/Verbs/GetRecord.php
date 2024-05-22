<?php

namespace App\Services\Oai\Verbs;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\Doi;
use App\Services\Oai\Arguments\Identifier;
use App\Services\Oai\Arguments\MetadataPrefix;
use App\Services\Oai\Arguments\Verb;
use App\Services\Oai\Exceptions\IdDoesNotExist;

/**
 * Represent the GetRecord oai verb
 * GetRecord return an unique record based on the Identifier argument
 * Class GetRecord
 * @package App\Services\Oai\Verbs
 */
class GetRecord implements OaiVerbInterface
{
    use OaiVerbTrait;

    /**
     * @see OaiVerbInterface
     */
    public function setArguments()
    {
        $this->arguments = [
            'verb' => new Verb($this),
            'metadataPrefix' => new MetadataPrefix($this),
            'identifier' => new Identifier($this)
        ];
    }

    /**
     * @see OaiVerbInterface
     * @return Response
     */
    public function getXmlResponse(): Response
    {
        /**
         * Get the doi by uri
         * @var Doi $doi
         */
        $doi = $this->entityManager->getRepository(Doi::class)->findOneBy(['uri' => $this->arguments['identifier']->getValue()]);

        // If no record return the oai exception
        if (!$doi) {
            return (new IdDoesNotExist())->setRequest($this->request)->setOaiService($this->oaiService)->getXmlResponse();
        }

        // Else create the GetRecord content
        $getRecord = $this->oaiService->createElement('GetRecord');

        $record = $this->oaiService->createElement('record');
        $record->appendChild($this->getHeader($doi));

        if (!$this->isDeleted($doi)) {
            $metadata = $this->oaiService->createElement('metadata');
            $metadata->appendChild($this->getOaiDCContent($doi));
            $record->appendChild($metadata);
        }

        $getRecord->appendChild($record);

        return $this->oaiService->formatResponse($this->request, $getRecord);
    }
}
