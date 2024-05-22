<?php

namespace App\Services\Oai\Arguments;

use App\Services\Oai\Verbs\OaiVerbInterface;

class Verb implements ArgumentInterface
{
    use ArgumentTrait;

    public function __construct(OaiVerbInterface $verb)
    {
        $this->verb = $verb;
        $this->name = 'verb';
        $this->setValue($verb->getRequest()->get('verb')?? $verb->getRequest()->get('amp;verb'));
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
        return false;
    }
}
