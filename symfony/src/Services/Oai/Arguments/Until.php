<?php

namespace App\Services\Oai\Arguments;

use App\Services\Oai\Verbs\OaiVerbInterface;

class Until implements ArgumentInterface
{
    use ArgumentTrait;

    public function __construct(OaiVerbInterface $verb)
    {
        $this->verb = $verb;
        $this->name = 'until';
        $this->setValue($verb->getRequest()->get('until')?? $verb->getRequest()->get('amp;until'));
    }

    /**
     * @see ArgumentInterface
     * @return bool
     */
    public function isRequired(): bool
    {
        return false;
    }

    /**
     * @see ArgumentInterface
     * @return bool
     */
    public function isInResumptionToken(): bool
    {
        return true;
    }
}
