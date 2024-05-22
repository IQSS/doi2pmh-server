<?php

namespace App\Services\Oai\Arguments;

use App\Services\Oai\Verbs\ListMetadataFormats;
use App\Services\Oai\Verbs\OaiVerbInterface;

class Identifier implements ArgumentInterface
{
    use ArgumentTrait;

    public function __construct(OaiVerbInterface $verb)
    {
        $this->verb = $verb;
        $this->name = 'identifier';
        $this->value = $verb->getRequest()->get('identifier')?? $verb->getRequest()->get('amp;identifier');
    }

    /**
     * @see ArgumentInterface
     * @return bool
     */
    public function isRequired(): bool
    {
        // Is optional for listMetadataFormat verb
        return get_class($this->verb) !== ListMetadataFormats::class;
    }

    /**
     * @see ArgumentInterface
     * @return bool
     */
    public function isInResumptionToken(): bool
    {
        return false;
    }
}
