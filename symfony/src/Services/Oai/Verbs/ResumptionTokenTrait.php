<?php

namespace App\Services\Oai\Verbs;

/**
 * Trait used to manage resumption token implementation
 * Trait ResumptionTokenTrait
 * @package App\Services\Oai\Verbs
 */
trait ResumptionTokenTrait
{
    public function updateResumptionToken(int $listSize){
        $this->arguments['resumptionToken']->setListSize($listSize);

        $this->arguments['resumptionToken']->incrementCursor();
    }
}
