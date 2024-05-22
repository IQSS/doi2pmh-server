<?php

namespace App\Services\Oai\Verbs;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use App\Entity\Doi;
use App\Services\Oai\Arguments\ResumptionToken;

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
