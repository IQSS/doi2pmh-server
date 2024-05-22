<?php

namespace App\Services\Oai\Arguments;

use App\Services\Oai\Verbs\OaiVerbInterface;

class From implements ArgumentInterface
{
    use ArgumentTrait;

    public function __construct(OaiVerbInterface $verb)
    {
        $this->verb = $verb;
        $this->name = 'from';
        $this->setValue($verb->getRequest()->get('from')?? $verb->getRequest()->get('amp;from'));
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
