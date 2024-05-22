<?php

namespace App\Services\Oai\Verbs;

use Symfony\Component\HttpFoundation\Response;
use App\Services\Oai\Arguments\Identifier;
use App\Services\Oai\Arguments\MetadataPrefix;
use App\Services\Oai\Arguments\Verb;

/**
 * Represent the ListMetadataFormats oai verb
 * ListMetadataFormats return all allowed metadata formats with there schema and namespace.
 * Class ListMetadataFormats
 * @package App\Services\Oai\Verbs
 */
class ListMetadataFormats implements OaiVerbInterface
{
    use OaiVerbTrait;

    /**
     * For now there is only oai_dc format which is allowed
     * @see OaiVerbInterface
     */
    public function setArguments()
    {
        $this->arguments = [
            'verb' => new Verb($this),
            'Identifier' => new Identifier($this)
        ];
    }

    /**
     * @see OaiVerbInterface
     * @return Response
     */
    public function getXmlResponse(): Response
    {
        $listMetadataFormats = $this->oaiService->createElement('ListMetadataFormats');

        foreach (MetadataPrefix::METADATA_FORMATS as $prefix => $format) {
            $metadataformats = $this->oaiService->createElement('metadataFormat');
            $metadataformats->appendChild($this->oaiService->createElement('metadataPrefix', $prefix));
            $metadataformats->appendChild($this->oaiService->createElement('schema', $format['schema']));
            $metadataformats->appendChild($this->oaiService->createElement('metadataNamespace', $format['metadataNamespace']));
            $listMetadataFormats->appendChild($metadataformats);
        }

        return $this->oaiService->formatResponse($this->request, $listMetadataFormats);
    }
}
