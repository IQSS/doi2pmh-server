<?php

namespace App\Services\Oai\Arguments;

use App\Services\Oai\Verbs\OaiVerbInterface;

class MetadataPrefix implements ArgumentInterface
{
    use ArgumentTrait;

    const METADATA_FORMATS = [
        'oai_dc' => [
            'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
            'metadataNamespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/'
        ]
    ];

    public function __construct(OaiVerbInterface $verb)
    {
        $this->verb = $verb;
        $this->name = 'metadataPrefix';
        $this->setValue($verb->getRequest()->get('metadataPrefix')?? $verb->getRequest()->get('amp;metadataPrefix'));
    }

    /**
     * @see ArgumentInterface
     * @return bool
     */
    public function isRequired(): bool
    {
        return true;
    }

    /**
     * @see ArgumentInterface
     * @return bool
     */
    public function isInResumptionToken(): bool
    {
        return true;
    }

    /**
     * @return bool     Return true if the argument value is an allowed metadata format
     */
    public function isAllowedFormat(): bool
    {
        return in_array($this->value, array_keys(self::METADATA_FORMATS));
    }
}
